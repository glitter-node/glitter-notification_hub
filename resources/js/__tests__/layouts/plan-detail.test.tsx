import { describe, expect, it } from 'vitest';
import layout from '../../../layouts/admin/plan_detail.json';
import { filterLayoutForPermissions, layoutHarness, serialized } from './harness';

const read = 'glitter-notification_hub.plans.read';
const cancel = 'glitter-notification_hub.plans.cancel';
const retry = 'glitter-notification_hub.plans.retry';

describe('plan detail', () => {
  it('renders safe detail and retry contract', async () => {
    const t = layoutHarness(layout);
    t.mockApi('plan', { data: { id: 'p1', status: 'failed', status_label: 'Failed', definition_type: 'booking' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    const { container } = await t.render();
    expect(container.textContent).toContain('booking');
    const s = serialized(layout);
    expect(s).toContain('/retry');
    expect(s).not.toContain('request_id');
    expect(s).not.toMatch(/recipient_snapshot|claim_token|policy_snapshot/);
    t.cleanup();
  });

  it('removes write buttons, modals, and api calls for read-only permissions', async () => {
    const filtered = filterLayoutForPermissions(layout, [read]);
    expect(serialized(filtered)).not.toMatch(/cancel_plan_button|retry_plan_button/);
    expect(serialized(filtered)).not.toMatch(/cancel_plan_modal|retry_plan_modal/);
    expect(serialized(filtered)).not.toMatch(/\/cancel|\/retry/);
    const t = layoutHarness(layout, {}, [read]);
    t.mockApi('plan', { data: { id: 'p1', status: 'failed', status_label: 'Failed' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    await t.render();
    expect((globalThis.fetch as any).mock.calls.filter(([, options]: any[]) => options?.method === 'POST')).toHaveLength(0);
    t.cleanup();
  });

  it('shows only cancel UI for cancel permission and valid state', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, cancel]);
    expect(serialized(filtered)).toContain('cancel_plan_modal');
    expect(serialized(filtered)).not.toContain('retry_plan_modal');
    const t = layoutHarness(layout, {}, [read, cancel]);
    t.mockApi('plan', { data: { id: 'p1', status: 'pending', status_label: 'Pending' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    const { container } = await t.render();
    expect(container.querySelector('#cancel_plan_button')).not.toBeNull();
    expect(container.querySelector('#retry_plan_button')).toBeNull();
    t.cleanup();
  });

  it('shows only retry UI for retry permission and valid state', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, retry]);
    expect(serialized(filtered)).toContain('retry_plan_modal');
    expect(serialized(filtered)).not.toContain('cancel_plan_modal');
    const t = layoutHarness(layout, {}, [read, retry]);
    t.mockApi('plan', { data: { id: 'p1', status: 'failed', status_label: 'Failed' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    const { container } = await t.render();
    expect(container.querySelector('#retry_plan_button')).not.toBeNull();
    expect(container.querySelector('#cancel_plan_button')).toBeNull();
    t.cleanup();
  });

  it('hides both actions in an incompatible state even with both write permissions', async () => {
    const t = layoutHarness(layout, {}, [read, cancel, retry]);
    t.mockApi('plan', { data: { id: 'p1', status: 'accepted', status_label: 'Accepted' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    const { container } = await t.render();
    expect(container.querySelector('#cancel_plan_button')).toBeNull();
    expect(container.querySelector('#retry_plan_button')).toBeNull();
    t.cleanup();
  });

  it('submits one mocked retry request without request_id', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, retry]);
    const t = layoutHarness(layout, {}, [read, retry]);
    t.mockApi('plan', { data: { id: 'p1', status: 'failed', status_label: 'Failed' } });
    t.mockApi('plan_attempts', { data: { data: [] } });
    t.mockApi('retry', { data: { plan_id: 'new-plan' } });
    await t.render();
    const action = (filtered.modals[0].children.at(-1) as any).actions[0].actions[1];
    (globalThis.fetch as any).mockClear();
    await t.triggerAction(action);
    const calls = (globalThis.fetch as any).mock.calls.filter(([url]: any[]) => url.endsWith('/retry'));
    expect(calls).toHaveLength(1);
    const [, options] = calls[0];
    expect(options.body).not.toContain('request_id');
    expect(options.body).toContain('retry_policy');
    t.cleanup();
  });
});
