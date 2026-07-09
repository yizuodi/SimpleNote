import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// base 设为 '/spa/'，与线上部署结构一致：
// 构建产物引用 /spa/assets/...，部署到 /www/wwwroot/note/spa/assets/。
export default defineConfig({
  base: '/spa/',
  plugins: [vue()],
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: false,
  },
})
