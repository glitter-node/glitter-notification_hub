import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'node:path';

export default defineConfig({
  plugins: [react()],
  test: { environment: 'happy-dom', include: ['resources/js/__tests__/layouts/*.test.tsx'], fileParallelism: false },
  resolve: { alias: { '@core': path.resolve(__dirname, '../../../resources/js/core') } },
});
