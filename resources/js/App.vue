<template>
  <div id="app" class="app-container">
    <!-- 现代化的侧边栏 -->
    <aside class="sidebar" :class="{ 'collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <el-icon><shop /></el-icon>
          </div>
          <span class="logo-text" v-show="!sidebarCollapsed">Shopify导入工具</span>
        </div>
        <el-button 
          class="collapse-btn" 
          circle 
          size="small" 
          @click="toggleSidebar"
        >
          <el-icon><fold v-if="!sidebarCollapsed" /><expand v-else /></el-icon>
        </el-button>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item" :class="{ active: currentRoute === '/' }">
          <el-icon><home-filled /></el-icon>
          <span v-show="!sidebarCollapsed">首页</span>
        </router-link>
        <router-link to="/templates" class="nav-item" :class="{ active: currentRoute === '/templates' }">
          <el-icon><document /></el-icon>
          <span v-show="!sidebarCollapsed">模板管理</span>
        </router-link>
      </nav>
    </aside>

    <!-- 主内容区域 -->
    <main class="main-content" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <!-- 现代化的顶部栏 -->
      <header class="top-header">
        <div class="header-content">
          <div class="page-title">
            <h1>{{ getPageTitle() }}</h1>
            <div class="breadcrumb">
              <span class="breadcrumb-item">{{ getBreadcrumb() }}</span>
            </div>
          </div>
          <div class="header-actions">
            <el-button class="user-avatar" circle>
              <el-icon><user /></el-icon>
            </el-button>
          </div>
        </div>
      </header>

      <!-- 页面内容 -->
      <div class="page-content">
        <router-view v-slot="{ Component }">
          <transition name="page-transition" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>

    <!-- 背景装饰 -->
    <div class="bg-decoration">
      <div class="decoration-blob blob-1"></div>
      <div class="decoration-blob blob-2"></div>
      <div class="decoration-blob blob-3"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { 
  Shop, 
  Fold, 
  Expand,
  HomeFilled, 
  Document, 
  User 
} from '@element-plus/icons-vue';

const route = useRoute();
const router = useRouter();
const currentRoute = computed(() => route.path);
const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const getPageTitle = () => {
  const titleMap = {
    '/': '商品上传',
    '/templates': '模板管理'
  };
  return titleMap[route.path] || 'Shopify导入工具';
};

const getBreadcrumb = () => {
  const breadcrumbMap = {
    '/': '批量上传商品文件夹，快速生成Shopify商品',
    '/templates': '创建和管理商品模板'
  };
  return breadcrumbMap[route.path] || '';
};

onMounted(() => {
  console.log('App mounted, current route:', route.path);
});

watch(() => route.path, (newPath) => {
  console.log('Route changed to:', newPath);
});
</script>

<style>
/* 全局样式重置和现代化设计变量 */
:root {
  --primary-color: #6366f1;
  --primary-dark: #4f46e5;
  --primary-light: #818cf8;
  --secondary-color: #f59e0b;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --error-color: #ef4444;
  --text-primary: #1f2937;
  --text-secondary: #6b7280;
  --text-muted: #9ca3af;
  --bg-primary: #ffffff;
  --bg-secondary: #f9fafb;
  --bg-tertiary: #f3f4f6;
  --border-light: #e5e7eb;
  --border-medium: #d1d5db;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --transition-fast: 0.15s ease;
  --transition-normal: 0.25s ease;
  --transition-slow: 0.4s ease;
  --sidebar-width: 280px;
  --sidebar-collapsed-width: 64px;
  --header-height: 80px;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
  line-height: 1.6;
  color: var(--text-primary);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  overflow-x: hidden;
}

.app-container {
  position: relative;
  min-height: 100vh;
  display: flex;
}

/* 现代化侧边栏 */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(255, 255, 255, 0.2);
  transition: width var(--transition-normal);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar.collapsed {
  width: var(--sidebar-collapsed-width);
}

.sidebar-header {
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border-light);
  min-height: var(--header-height);
  background: rgba(248, 250, 252, 0.8);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  flex-shrink: 0;
}

.logo-text {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  transition: opacity var(--transition-normal);
}

.collapse-btn {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-light);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.collapse-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: scale(1.05);
}

.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all var(--transition-fast);
  position: relative;
  margin: 2px 0;
  border-radius: var(--radius-md);
  font-weight: 500;
  min-height: 48px;
}

.nav-item:hover {
  background: rgba(99, 102, 241, 0.08);
  color: var(--primary-color);
  transform: translateX(4px);
}

.nav-item.active {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  box-shadow: var(--shadow-md);
  transform: translateX(4px);
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: var(--primary-color);
  border-radius: 2px;
}

.nav-item .el-icon {
  font-size: 20px;
  min-width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-item span {
  font-weight: 500;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  transition: opacity var(--transition-normal);
}

/* 主内容区域 */
.main-content {
  flex: 1;
  margin-left: var(--sidebar-width);
  transition: margin-left var(--transition-normal);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content.sidebar-collapsed {
  margin-left: var(--sidebar-collapsed-width);
}

/* 现代化顶部栏 */
.top-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  height: var(--header-height);
  display: flex;
  align-items: center;
  padding: 0 32px;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.header-content {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-title h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.2;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.breadcrumb {
  margin-top: 6px;
}

.breadcrumb-item {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 400;
  line-height: 1.4;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  background: var(--bg-tertiary);
  border: none;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.user-avatar:hover {
  background: var(--primary-color);
  color: white;
}

/* 页面内容 */
.page-content {
  flex: 1;
  padding: 32px;
  background: rgba(255, 255, 255, 0.02);
}

/* 页面切换动画 */
.page-transition-enter-active,
.page-transition-leave-active {
  transition: all var(--transition-normal);
}

.page-transition-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-transition-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* 背景装饰 */
.bg-decoration {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: -1;
  overflow: hidden;
}

.decoration-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.1;
  animation: float 20s ease-in-out infinite;
}

.blob-1 {
  width: 300px;
  height: 300px;
  background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
  top: 10%;
  right: 10%;
  animation-delay: 0s;
}

.blob-2 {
  width: 400px;
  height: 400px;
  background: linear-gradient(45deg, var(--success-color), var(--primary-light));
  bottom: 10%;
  left: 10%;
  animation-delay: 10s;
}

.blob-3 {
  width: 250px;
  height: 250px;
  background: linear-gradient(45deg, var(--warning-color), var(--error-color));
  top: 50%;
  left: 50%;
  animation-delay: 5s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* 响应式设计 */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform var(--transition-normal);
  }
  
  .sidebar.mobile-open {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .page-content {
    padding: 20px;
  }
}

/* Element Plus 组件样式覆盖 */
.el-button {
  border-radius: var(--radius-md);
  font-weight: 500;
  transition: all var(--transition-fast);
}

.el-button--primary {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  border: none;
  box-shadow: var(--shadow-sm);
}

.el-button--primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.el-card {
  border-radius: var(--radius-lg);
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  box-shadow: var(--shadow-md);
  transition: all var(--transition-normal);
}

.el-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}
</style> 