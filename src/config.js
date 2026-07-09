// 站点展示性文案：单一事实源。
// 运行时优先读取由 index.php 注入的 window.__NOTE_CONFIG__（见部署的 HTML 壳）；
// 缺失时（如本地 dev）回退到下方默认值。生产环境改文案请改 index.php 里的 $site。
const fallback = {
  email: 'feedback#example.com', // 反馈邮箱（# 代替 @ 防爬）
  icp: '', // ICP 备案号（中国大陆站点填写）
  icpUrl: 'https://beian.miit.gov.cn/',
  siteName: 'SimpleNote',
  copyright: 'Copyright © 2024 SimpleNote',
  // 首页公告：items 为空则整个 notice 不显示；text 支持 {email}/{siteName} 占位符
  notice: {
    icon: '📢',
    items: [
      { label: '隐私提醒', text: '任何拥有分享链接的访客都可以查看和编辑您分享的内容。' },
      { label: '滥用反馈邮箱', text: '{email}（烦请将 # 替换为 @），我们承诺收到反馈后 24 小时内处理。' },
    ],
  },
}

const c = (typeof window !== 'undefined' && window.__NOTE_CONFIG__) || {}

export const config = {
  email: c.email ?? fallback.email,
  icp: c.icp ?? fallback.icp,
  // 线上 $site 用的键是 icp_url
  icpUrl: c.icp_url ?? fallback.icpUrl,
  siteName: c.siteName ?? fallback.siteName,
  copyright: c.copyright ?? fallback.copyright,
  notice: c.notice ?? fallback.notice,
}
