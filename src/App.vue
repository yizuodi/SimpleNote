<script setup>
import { ref, reactive, computed, provide, onMounted } from 'vue'
import { config } from './config'
import Home from './components/Home.vue'
import Editor from './components/Editor.vue'

// 主题（亮/暗）
const isDark = ref(false)
function toggleTheme() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('note-theme', isDark.value ? 'dark' : 'light')
}

// 当前 note id：从 URL 路径解析，合法则进入编辑页，否则首页
const noteId = computed(() => {
  const p = window.location.pathname.replace(/^\//, '').replace(/\/$/, '')
  return p && /^[a-zA-Z0-9_-]+$/.test(p) ? p : null
})

// Toast（provide 给子组件）
const toast = reactive({ show: false, msg: '', type: 'info' })
let toastTimer = null
function showToast(msg, type = 'info', duration = 2000) {
  toast.msg = msg
  toast.type = type
  toast.show = true
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.show = false
  }, duration)
}

provide('toast', showToast)
provide('isDark', isDark)

onMounted(() => {
  const saved = localStorage.getItem('note-theme')
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})
</script>

<template>
  <div class="app-layout" :class="{ dark: isDark }">
    <header class="app-header">
      <div class="header-inner">
        <a href="/" class="logo">
          <span class="logo-icon">📝</span>
          <span class="logo-text">{{ config.siteName }}</span>
        </a>
        <div class="header-actions">
          <button
            class="icon-btn"
            @click="toggleTheme"
            :title="isDark ? '切换亮色模式' : '切换暗色模式'"
          >
            {{ isDark ? '☀️' : '🌙' }}
          </button>
        </div>
      </div>
    </header>

    <main class="app-main">
      <Editor v-if="noteId" :note-id="noteId" />
      <Home v-else />
    </main>

    <footer class="app-footer">
      <p>{{ config.copyright }}</p>
      <p>
        <a :href="config.icpUrl" target="_blank" rel="noopener">{{ config.icp }}</a>
        · 反馈邮箱：{{ config.email }}
      </p>
    </footer>

    <Teleport to="body">
      <Transition name="slide-up">
        <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.msg }}</div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid var(--border);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  background: #fffc;
}
html.dark .app-header {
  background: #1e293bd9;
}
.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  height: 56px;
  margin: 0 auto;
  padding: 0 24px;
}
.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text);
  font-size: 1.1rem;
  font-weight: 700;
  text-decoration: none;
}
.logo-icon {
  font-size: 1.3rem;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.icon-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg);
  cursor: pointer;
  font-size: 1.1rem;
  transition: all var(--transition);
}
.icon-btn:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-sm);
}
.app-main {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px;
}
.app-footer {
  text-align: center;
  color: var(--text-secondary);
  border-top: 1px solid var(--border);
  padding: 24px;
  font-size: 0.8rem;
  line-height: 1.8;
}
.app-footer a {
  color: var(--text-secondary);
  text-decoration: none;
  transition: color var(--transition);
}
.app-footer a:hover {
  color: var(--primary);
}
.toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translate(-50%);
  z-index: 9999;
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-lg);
  padding: 12px 24px;
  font-size: 0.9rem;
}
.toast.info {
  background: var(--primary);
  color: #fff;
}
.toast.success {
  color: #fff;
  background: #22c55e;
}
.toast.error {
  color: #fff;
  background: #ef4444;
}
@media (width <= 640px) {
  .header-inner {
    padding: 0 12px;
  }
  .app-main {
    padding: 20px 16px;
  }
  .logo {
    gap: 6px;
    font-size: 0.95rem;
  }
  .logo-icon {
    font-size: 1.1rem;
  }
}
@media (width <= 360px) {
  .logo-text {
    display: none;
  }
}
</style>
