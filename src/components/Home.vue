<script setup>
import { ref, computed, inject } from 'vue'
import { config } from '../config'

const toast = inject('toast')
const origin = window.location.origin

const showCustom = ref(false)
const customName = ref(null)

// 随机链接字符集（去掉易混淆的 0/o/1/l/i 等）
const CHARS = '234579abcdefghjkmnpqrstwxyz'
function randomName() {
  let s = ''
  for (let i = 0; i < 5; i++) s += CHARS[Math.floor(Math.random() * CHARS.length)]
  return s
}
const randomPreview = ref(randomName())

function goRandom() {
  window.location.href = '/' + randomName()
}
const isValid = computed(() => customName.value && /^[a-zA-Z0-9_-]+$/.test(customName.value))
function openCustom() {
  if (!isValid.value) {
    toast('名称格式不正确', 'error')
    return
  }
  window.location.href = '/' + customName.value
}
</script>

<template>
  <div class="home">
    <section class="notice">
      <div class="notice-icon">📢</div>
      <div class="notice-body">
        <p><strong>隐私提醒：</strong>任何拥有分享链接的访客都可以查看和编辑您分享的内容。</p>
        <p>
          <strong>滥用反馈邮箱：</strong><code>{{ config.email }}</code>
          （烦请将 # 替换为 @），我们承诺收到反馈后 24 小时内处理。
        </p>
      </div>
    </section>

    <section class="hero">
      <h1 class="hero-title"><span class="gradient-text">快速分享文本</span></h1>
      <p class="hero-desc">无需登录，打开即用。输入内容，自动保存，分享链接即可访问。</p>
    </section>

    <section class="actions">
      <a :href="'/' + randomPreview" class="action-card" @click.prevent="goRandom">
        <div class="action-icon">✨</div>
        <div class="action-body">
          <h3>新建分享</h3>
          <p>随机生成链接，打开即可编辑</p>
        </div>
        <div class="action-arrow">→</div>
      </a>
      <div class="action-card" @click="showCustom = true">
        <div class="action-icon">🔗</div>
        <div class="action-body">
          <h3>自定义链接</h3>
          <p>指定一个易记的链接名称</p>
        </div>
        <div class="action-arrow">→</div>
      </div>
    </section>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showCustom" class="modal-overlay" @click.self="showCustom = false">
          <div class="modal">
            <h3>自定义链接</h3>
            <div class="input-group">
              <span class="input-prefix">{{ origin }}/</span>
              <input
                v-model="customName"
                @keyup.enter="openCustom"
                placeholder="输入自定义名称"
                class="input"
                maxlength="64"
                autofocus
              />
            </div>
            <p class="hint">仅支持字母、数字、下划线和连字符，最长64位</p>
            <div class="modal-actions">
              <button class="btn btn-ghost" @click="showCustom = false">取消</button>
              <button class="btn btn-primary" @click="openCustom" :disabled="!isValid">打开</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <section class="features">
      <div class="feature">
        <span class="feature-icon">⚡</span>
        <h4>自动保存</h4>
        <p>输入内容自动保存到服务器，无需手动操作</p>
      </div>
      <div class="feature">
        <span class="feature-icon">🔒</span>
        <h4>隐私提示</h4>
        <p>任何拥有链接的人都可以查看和编辑，请勿分享敏感信息</p>
      </div>
      <div class="feature">
        <span class="feature-icon">📱</span>
        <h4>全平台适配</h4>
        <p>手机、平板、电脑均可流畅使用</p>
      </div>
      <div class="feature">
        <span class="feature-icon">🖨️</span>
        <h4>支持打印</h4>
        <p>编辑页可直接打印内容，格式整洁</p>
      </div>
    </section>

    <section class="api-section">
      <details>
        <summary><strong>API 说明</strong> — 支持 curl 命令行操作</summary>
        <div class="api-content">
          <div class="code-block">
            <code># 读取内容<br />curl {{ origin }}/你的笔记名<br /><br /># 写入内容<br />curl -X POST -d 'text=Hello World' {{ origin }}/你的笔记名<br /><br /># 获取原始文本<br />curl {{ origin }}/你的笔记名?raw</code>
          </div>
        </div>
      </details>
    </section>
  </div>
</template>

<style scoped>
.home {
  display: flex;
  flex-direction: column;
  gap: 40px;
  max-width: 720px;
  margin: 0 auto;
}
.notice {
  display: flex;
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  border-left: 4px solid var(--primary);
  border-radius: var(--radius);
  gap: 14px;
  padding: 18px 20px;
  line-height: 1.7;
}
.notice-icon {
  flex-shrink: 0;
  margin-top: 2px;
  font-size: 1.3rem;
}
.notice-body p {
  color: var(--text-secondary);
  margin-bottom: 2px;
  font-size: 0.88rem;
}
.notice-body p:last-child {
  margin-bottom: 0;
}
.notice-body strong {
  color: var(--text);
}
.notice-body code {
  font-family: var(--font-mono);
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1px 6px;
  font-size: 0.84rem;
}
.hero {
  text-align: center;
  padding-top: 20px;
}
.hero-title {
  margin-bottom: 16px;
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1.2;
}
.gradient-text {
  background: linear-gradient(135deg, var(--primary), #a855f7, #ec4899);
  -webkit-text-fill-color: transparent;
  -webkit-background-clip: text;
  background-clip: text;
}
.hero-desc {
  color: var(--text-secondary);
  font-size: 1.1rem;
  line-height: 1.6;
}
.actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.action-card {
  display: flex;
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  cursor: pointer;
  transition: all var(--transition);
  color: inherit;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  text-decoration: none;
}
.action-card:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}
.action-icon {
  flex-shrink: 0;
  font-size: 1.6rem;
}
.action-body {
  flex: 1;
}
.action-body h3 {
  margin-bottom: 2px;
  font-size: 1rem;
  font-weight: 600;
}
.action-body p {
  color: var(--text-secondary);
  font-size: 0.85rem;
}
.action-arrow {
  color: var(--text-secondary);
  transition: transform var(--transition);
  font-size: 1.2rem;
}
.action-card:hover .action-arrow {
  color: var(--primary);
  transform: translate(4px);
}
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: #0006;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
}
.modal {
  background: var(--bg-secondary);
  border-radius: var(--radius);
  width: 100%;
  max-width: 480px;
  box-shadow: var(--shadow-lg);
  padding: 28px;
}
.modal h3 {
  margin-bottom: 16px;
  font-size: 1.1rem;
}
.input-group {
  display: flex;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  overflow: hidden;
}
.input-prefix {
  display: flex;
  background: var(--bg);
  color: var(--text-secondary);
  white-space: nowrap;
  border-right: 1px solid var(--border);
  align-items: center;
  padding: 10px 12px;
  font-size: 0.9rem;
}
.input {
  color: var(--text);
  font-size: 0.9rem;
  font-family: var(--font-mono);
  background: 0 0;
  border: none;
  outline: none;
  flex: 1;
  padding: 10px 12px;
}
.hint {
  color: var(--text-secondary);
  margin-top: 8px;
  font-size: 0.8rem;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 20px;
}
.btn {
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition);
  border: 1px solid #0000;
  padding: 8px 20px;
  font-size: 0.9rem;
  font-weight: 500;
}
.btn-ghost {
  border-color: var(--border);
  color: var(--text);
  background: 0 0;
}
.btn-ghost:hover {
  border-color: var(--text-secondary);
}
.btn-primary {
  background: var(--primary);
  color: #fff;
}
.btn-primary:hover {
  background: var(--primary-hover);
}
.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.features {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.feature {
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
}
.feature-icon {
  display: block;
  margin-bottom: 8px;
  font-size: 1.5rem;
}
.feature h4 {
  margin-bottom: 4px;
  font-size: 0.95rem;
  font-weight: 600;
}
.feature p {
  color: var(--text-secondary);
  font-size: 0.82rem;
  line-height: 1.5;
}
.api-section details {
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 20px;
}
.api-section summary {
  cursor: pointer;
  color: var(--text-secondary);
  -webkit-user-select: none;
  user-select: none;
  font-size: 0.9rem;
}
.api-content {
  margin-top: 12px;
}
.code-block {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 16px;
  overflow-x: auto;
}
.code-block code {
  font-family: var(--font-mono);
  white-space: pre;
  color: var(--text);
  font-size: 0.82rem;
  line-height: 1.7;
}
@media (width <= 640px) {
  .hero-title {
    font-size: 1.8rem;
  }
  .hero-desc {
    font-size: 0.95rem;
  }
  .features {
    grid-template-columns: 1fr;
  }
  .input-prefix {
    display: none;
  }
}
</style>
