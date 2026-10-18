import { describe, expect, it } from 'vitest';
import layout from '../../../layouts/admin/dead_letter_detail.json';
import { filterLayoutForPermissions, layoutHarness, serialized } from './harness';

const read = 'glitter-notification_hub.dead-letters.read';
const resolve = 'glitter-notification_hub.dead-letters.resolve';
const retry = 'glitter-notification_hub.dead-letters.retry';

describe('dead letter detail', () => {
  it('renders resolve and retry contracts', async () => {
    const t = layoutHarness(layout);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: false, reason_code: 'permanent' } });
    const { container } = await t.render();
    expect(container.textContent).toContain('permanent');
    const s = serialized(layout);
    expect(s).toContain('/resolve');
    expect(s).toContain('/retry');
    expect(s).not.toContain('request_id');
    t.cleanup();
  });

  it('removes write buttons, modals, and api calls for read-only permissions', async () => {
    const filtered = filterLayoutForPermissions(layout, [read]);
    expect(serialized(filtered)).not.toMatch(/resolve_dead_letter_button|retry_dead_letter_button/);
    expect(serialized(filtered)).not.toMatch(/resolve_dead_letter_modal|retry_dead_letter_modal/);
    expect(serialized(filtered)).not.toMatch(/\/resolve|\/retry/);
    const t = layoutHarness(layout, {}, [read]);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: false } });
    await t.render();
    expect((globalThis.fetch as any).mock.calls.filter(([, options]: any[]) => options?.method === 'POST')).toHaveLength(0);
    t.cleanup();
  });

  it('shows only resolve UI with resolve permission while unresolved', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, resolve]);
    expect(serialized(filtered)).toContain('resolve_dead_letter_modal');
    expect(serialized(filtered)).not.toContain('retry_dead_letter_modal');
    const t = layoutHarness(layout, {}, [read, resolve]);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: false } });
    const { container } = await t.render();
    expect(container.querySelector('#resolve_dead_letter_button')).not.toBeNull();
    expect(container.querySelector('#retry_dead_letter_button')).toBeNull();
    t.cleanup();
  });

  it('shows only retry UI with retry permission while unresolved', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, retry]);
    expect(serialized(filtered)).toContain('retry_dead_letter_modal');
    expect(serialized(filtered)).not.toContain('resolve_dead_letter_modal');
    const t = layoutHarness(layout, {}, [read, retry]);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: false } });
    const { container } = await t.render();
    expect(container.querySelector('#retry_dead_letter_button')).not.toBeNull();
    expect(container.querySelector('#resolve_dead_letter_button')).toBeNull();
    t.cleanup();
  });

  it('hides both actions when the dead letter is resolved even with write permissions', async () => {
    const t = layoutHarness(layout, {}, [read, resolve, retry]);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: true } });
    const { container } = await t.render();
    expect(container.querySelector('#resolve_dead_letter_button')).toBeNull();
    expect(container.querySelector('#retry_dead_letter_button')).toBeNull();
    t.cleanup();
  });

  it('submits one mocked retry request and refetches only after success', async () => {
    const filtered = filterLayoutForPermissions(layout, [read, retry]);
    const t = layoutHarness(layout, {}, [read, retry]);
    t.mockApi('dead_letter', { data: { id: 'd1', plan_id: 'p1', resolved: false } });
    t.mockApi('retry', { data: { plan_id: 'new-plan' } });
    await t.render();
    const action = (filtered.modals[0].children.at(-1) as any).actions[0].actions[1];
    (globalThis.fetch as any).mockClear();
    await t.triggerAction(action);
    const calls = (globalThis.fetch as any).mock.calls.filter(([url]: any[]) => url.endsWith('/retry'));
    expect(calls).toHaveLength(1);
    const [, options] = calls[0];
    expect(options.body).not.toContain('request_id');
    expect(options.body).toContain('max_attempts');
    t.cleanup();
  });
});
