<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, inject } from 'vue'

const props = defineProps({
  noteId: { type: String, required: true },
})

const toast = inject('toast')

const content = ref('')
const textareaEl = ref(null)
const status = ref('idle') // idle | saving | saved | error
let lastSaved = ''
let debounceTimer = null

// 统计：字符 / 行 / 词
const stats = computed(() => {
  const t = content.value
  if (!t) return ''
  const words = t.trim() ? t.trim().split(/\s+/).length : 0
  return `${t.length} 字符 · ${t.split('\n').length} 行 · ${words} 词`
})

// 保存状态文案
const statusText = computed(() => {
  switch (status.value) {
    case 'saving':
      return '保存中...'
    case 'saved':
      return '已保存 ✓'
    case 'error':
      return '保存失败'
    default:
      return ''
  }
})

// 保存：POST 到当前路径，body 为 text=...
async function save() {
  if (content.value === lastSaved) return
  status.value = 'saving'
  try {
    const res = await fetch(window.location.pathname, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: 'text=' + encodeURIComponent(content.value),
    })
    if (!res.ok) throw new Error('Save failed')
    lastSaved = content.value
    status.value = 'saved'
    setTimeout(() => {
      if (status.value === 'saved') status.value = 'idle'
    }, 2000)
  } catch (e) {
    status.value = 'error'
    console.error('Save error:', e)
  }
}

// 内容变化：800ms 防抖后保存
watch(content, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(save, 800)
})

// 进入：加载已有内容
onMounted(async () => {
  try {
    const res = await fetch(window.location.pathname + '?raw')
    if (res.ok) {
      content.value = lastSaved = await res.text()
    }
  } catch (e) {
    console.error('Load error:', e)
  }
  textareaEl.value?.focus()
})

// 离开：冲刷最后一次保存
onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
  save()
})

function copyLink() {
  navigator.clipboard
    .writeText(window.location.href)
    .then(() => toast('链接已复制', 'success'))
    .catch(() => toast('复制失败', 'error'))
}
function copyContent() {
  navigator.clipboard
    .writeText(content.value)
    .then(() => toast('内容已复制', 'success'))
    .catch(() => toast('复制失败', 'error'))
}
function download() {
  const blob = new Blob([content.value], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = props.noteId + '.txt'
  a.click()
  URL.revokeObjectURL(url)
  toast('已下载 ' + props.noteId + '.txt', 'success')
}
function printContent() {
  const w = window.open('', '_blank')
  w.document.write('<pre style="font-size:14px;padding:24px;white-space:pre-wrap;word-break:break-word;">')
  w.document.write(content.value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
  w.document.write('</pre>')
  w.document.close()
  w.print()
}
function clearContent() {
  if (!confirm('确定要清空并删除此内容吗？此操作不可恢复。')) return
  content.value = ''
  save()
  toast('内容已清空', 'info')
}
// Tab 键插入 4 个空格
function onTab() {
  const el = textareaEl.value
  const start = el.selectionStart
  const end = el.selectionEnd
  content.value = content.value.substring(0, start) + '    ' + content.value.substring(end)
  requestAnimationFrame(() => {
    el.selectionStart = el.selectionEnd = start + 4
  })
}
</script>

<template>
  <div class="editor-page">
    <div class="toolbar">
      <div class="toolbar-left">
        <span class="note-id" @click="copyLink" title="点击复制链接"> 📎 {{ noteId }}</span>
        <span v-if="stats" class="stats">{{ stats }}</span>
      </div>
      <div class="toolbar-right">
        <span class="save-status" :class="status">{{ statusText }}</span>
        <button class="tool-btn" @click="copyContent" title="复制内容">📋</button>
        <button class="tool-btn" @click="download" title="下载">💾</button>
        <button class="tool-btn" @click="printContent" title="打印">🖨️</button>
        <button class="tool-btn danger" @click="clearContent" title="清空删除">🗑️</button>
      </div>
    </div>
    <div class="editor-container">
      <textarea
        ref="textareaEl"
        v-model="content"
        class="editor-textarea"
        placeholder="在此输入内容..."
        spellcheck="false"
        @keydown.tab.prevent="onTab"
      ></textarea>
    </div>
  </div>
</template>

<style scoped>
.editor-page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 136px);
  min-height: 400px;
}
.toolbar {
  display: flex;
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  border-radius: var(--radius) var(--radius) 0 0;
  border-bottom: none;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
}
.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.note-id {
  font-family: var(--font-mono);
  color: var(--primary);
  cursor: pointer;
  -webkit-user-select: none;
  user-select: none;
  font-size: 0.9rem;
  font-weight: 600;
}
.note-id:hover {
  text-decoration: underline;
}
.stats {
  color: var(--text-secondary);
  font-size: 0.78rem;
}
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 4px;
}
.save-status {
  color: var(--text-secondary);
  transition: all var(--transition);
  padding: 4px 8px;
  font-size: 0.78rem;
}
.save-status.saving {
  color: var(--primary);
}
.save-status.saved {
  color: #22c55e;
}
.save-status.error {
  color: #ef4444;
}
.tool-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  width: 32px;
  height: 32px;
  transition: all var(--transition);
  background: 0 0;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
}
.tool-btn:hover {
  background: var(--primary-light);
}
.tool-btn.danger:hover {
  background: #fef2f2;
}
html.dark .tool-btn.danger:hover {
  background: #451a1a;
}
.editor-container {
  border: 1px solid var(--border);
  border-radius: 0 0 var(--radius) var(--radius);
  background: var(--bg-secondary);
  flex: 1;
  display: flex;
  overflow: hidden;
}
.editor-textarea {
  resize: none;
  font-family: var(--font-mono);
  color: var(--text);
  tab-size: 4;
  background: 0 0;
  border: none;
  outline: none;
  flex: 1;
  padding: 20px;
  font-size: 0.92rem;
  line-height: 1.7;
}
.editor-textarea::placeholder {
  color: var(--text-secondary);
  opacity: 0.5;
}
@media (width <= 640px) {
  .editor-page {
    height: calc(100vh - 156px);
  }
  .toolbar {
    padding: 6px 10px;
  }
  .stats {
    display: none;
  }
  .editor-textarea {
    padding: 14px;
    font-size: 0.85rem;
  }
}
</style>
