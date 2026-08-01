import React from 'react';
import { ComponentRegistry } from '@core/template-engine/ComponentRegistry';
import { createLayoutTest } from '@core/template-engine/__tests__/utils/layoutTestUtils';

const Box = ({ children, label, title, text }: any) => <div>{title ?? label ?? text}{children}</div>;
const Input = (props: any) => <input aria-label={props.placeholder ?? props.value ?? 'input'} {...props} onChange={props.onChange ?? (() => {})}/>;
const Select = ({ options = [], ...props }: any) => <select {...props} onChange={props.onChange ?? (() => {})}>{options.map((o: any) => <option key={o.value} value={o.value}>{o.label}</option>)}</select>;
const Button = ({ children, text, ...props }: any) => <button {...props}>{text}{children}</button>;

function filterComponents(components: any[], permissions: Set<string>): any[] {
  return components.flatMap((component) => {
    const required = Array.isArray(component?.permissions) ? component.permissions : [];
    if (!required.every((permission: string) => permissions.has(permission))) return [];
    const filtered = { ...component };
    delete filtered.permissions;
    if (Array.isArray(filtered.children)) filtered.children = filterComponents(filtered.children, permissions);
    return [filtered];
  });
}

export function filterLayoutForPermissions(layout: any, grantedPermissions: string[]): any {
  const filtered = structuredClone(layout);
  const permissions = new Set(grantedPermissions);
  if (Array.isArray(filtered.components)) filtered.components = filterComponents(filtered.components, permissions);
  if (filtered.slots) {
    for (const [name, components] of Object.entries(filtered.slots)) {
      if (Array.isArray(components)) filtered.slots[name] = filterComponents(components, permissions);
    }
  }
  if (Array.isArray(filtered.modals)) filtered.modals = filterComponents(filtered.modals, permissions);
  return filtered;
}

export function layoutHarness(
  layout: any,
  initialData: Record<string, any> = {},
  grantedPermissions?: string[],
) {
  const registry = ComponentRegistry.getInstance();
  (registry as any).registry = {};
  for (const name of ['Div','P','Span','H2','Badge','Modal','PageHeader','Fragment']) {
    (registry as any).registry[name] = { component: Box, metadata: { name, type: name === 'PageHeader' || name === 'Modal' ? 'composite' : name === 'Fragment' ? 'layout' : 'basic' } };
  }
  (registry as any).registry.Input = { component: Input, metadata: { name: 'Input', type: 'basic' } };
  (registry as any).registry.Textarea = { component: (p: any) => <textarea {...p}/>, metadata: { name: 'Textarea', type: 'basic' } };
  (registry as any).registry.Select = { component: Select, metadata: { name: 'Select', type: 'basic' } };
  (registry as any).registry.Button = { component: Button, metadata: { name: 'Button', type: 'basic' } };
  const renderedLayout = grantedPermissions === undefined
    ? layout
    : filterLayoutForPermissions(layout, grantedPermissions);
  return createLayoutTest(renderedLayout, { componentRegistry: registry, initialData, routeParams: { id: '550e8400-e29b-41d4-a716-446655440000' } });
}

export function serialized(layout: any): string { return JSON.stringify(layout); }

export function allComponents(layout: any): any[] {
  const found: any[] = [];
  const visit = (components: any[]) => {
    for (const component of components ?? []) {
      found.push(component);
      visit(component.children);
    }
  };
  visit(layout.components);
  for (const components of Object.values(layout.slots ?? {})) visit(components as any[]);
  visit(layout.modals);
  return found;
}

export function componentById(layout: any, id: string): any {
  return allComponents(layout).find((component) => component.id === id);
}
