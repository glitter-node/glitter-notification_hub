import { describe, expect, it } from 'vitest';
import ko from '../../../lang/ko.json';
import en from '../../../lang/en.json';
import attempts from '../../../layouts/admin/attempts.json';
import attemptDetail from '../../../layouts/admin/attempt_detail.json';
import deadLetters from '../../../layouts/admin/dead_letters.json';
import deadLetterDetail from '../../../layouts/admin/dead_letter_detail.json';
import plans from '../../../layouts/admin/plans.json';
import planDetail from '../../../layouts/admin/plan_detail.json';

const layouts = [plans, planDetail, attempts, attemptDetail, deadLetters, deadLetterDetail];

function paths(value: unknown, prefix = ''): string[] {
  if (value === null || typeof value !== 'object' || Array.isArray(value)) return [prefix];
  return Object.entries(value).flatMap(([key, child]) => paths(child, prefix ? `${prefix}.${key}` : key));
}

describe('layout translations', () => {
  it('keeps Korean and English frontend keys in parity', () => {
    expect(paths(ko).sort()).toEqual(paths(en).sort());
  });

  it('defines every module translation key referenced by layouts', () => {
    const defined = new Set(paths(ko).map((key) => `glitter-notification_hub.${key}`));
    const references = JSON.stringify(layouts).match(/\$t:glitter-notification_hub\.[A-Za-z0-9_.-]+/g) ?? [];
    expect(references.length).toBeGreaterThan(0);
    for (const reference of references) expect(defined.has(reference.slice(3))).toBe(true);
  });

  it('uses only namespaced module keys or approved shared common keys', () => {
    const references = JSON.stringify(layouts).match(/\$t:[A-Za-z0-9_.-]+/g) ?? [];
    for (const reference of references) {
      expect(reference.startsWith('$t:glitter-notification_hub.admin.') || reference.startsWith('$t:common.')).toBe(true);
    }
  });
});
