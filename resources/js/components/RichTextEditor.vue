<template>
  <div class="rich-text-editor modern-editor" :class="{ 'editor-focused': isFocused }">
    <div class="editor-toolbar">
      <div class="toolbar-group">
        <button 
          type="button" 
          @click="formatText('bold')" 
          class="toolbar-btn"
          :class="{ active: isActive('bold') }"
          title="粗体"
        >
          <el-icon><semi-select /></el-icon>
        </button>
        <button 
          type="button" 
          @click="formatText('italic')" 
          class="toolbar-btn"
          :class="{ active: isActive('italic') }"
          title="斜体"
        >
          <el-icon><edit /></el-icon>
        </button>
        <button 
          type="button" 
          @click="formatText('underline')" 
          class="toolbar-btn"
          :class="{ active: isActive('underline') }"
          title="下划线"
        >
          <span class="underline-icon">U</span>
        </button>
      </div>
      
      <div class="toolbar-divider"></div>
      
      <div class="toolbar-group">
        <button 
          type="button" 
          @click="formatText('insertUnorderedList')" 
          class="toolbar-btn"
          title="无序列表"
        >
          <el-icon><list /></el-icon>
        </button>
        <button 
          type="button" 
          @click="formatText('insertOrderedList')" 
          class="toolbar-btn"
          title="有序列表"
        >
          <el-icon><menu /></el-icon>
        </button>
      </div>
      
      <div class="toolbar-divider"></div>
      
      <div class="toolbar-group">
        <button 
          type="button" 
          @click="insertLink" 
          class="toolbar-btn"
          title="插入链接"
        >
          <el-icon><link /></el-icon>
        </button>
        <button 
          type="button" 
          @click="clearFormatting" 
          class="toolbar-btn"
          title="清除格式"
        >
          <el-icon><close /></el-icon>
        </button>
      </div>
    </div>
    
    <div
      ref="editorRef"
      class="editor-content"
      contenteditable="true"
      :placeholder="placeholder"
      @input="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
      @keydown="handleKeydown"
      v-html="modelValue"
    ></div>
    
    <div class="editor-footer" v-if="showWordCount">
      <div class="word-count">
        字数: {{ wordCount }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { 
  SemiSelect, 
  Edit, 
  List, 
  Menu, 
  Link, 
  Close 
} from '@element-plus/icons-vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '请输入内容...'
  },
  showWordCount: {
    type: Boolean,
    default: false
  },
  maxHeight: {
    type: String,
    default: '300px'
  }
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const editorRef = ref(null);
const isFocused = ref(false);

const wordCount = computed(() => {
  const text = editorRef.value?.innerText || '';
  return text.trim().length;
});

const formatText = (command, value = null) => {
  document.execCommand(command, false, value);
  editorRef.value.focus();
  handleInput();
};

const isActive = (command) => {
  try {
    return document.queryCommandState(command);
  } catch (e) {
    return false;
  }
};

const insertLink = () => {
  const selection = window.getSelection();
  const selectedText = selection.toString();
  const url = prompt('请输入链接地址:', 'https://');
  
  if (url && url.trim()) {
    if (selectedText) {
      formatText('createLink', url);
    } else {
      const linkText = prompt('请输入链接文字:', url);
      if (linkText) {
        document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${linkText}</a>`);
        handleInput();
      }
    }
  }
};

const clearFormatting = () => {
  formatText('removeFormat');
  formatText('unlink');
};

const handleInput = () => {
  const content = editorRef.value.innerHTML;
  emit('update:modelValue', content);
};

const handleFocus = () => {
  isFocused.value = true;
  emit('focus');
};

const handleBlur = () => {
  isFocused.value = false;
  emit('blur');
};

const handleKeydown = (event) => {
  // 支持常用快捷键
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case 'b':
        event.preventDefault();
        formatText('bold');
        break;
      case 'i':
        event.preventDefault();
        formatText('italic');
        break;
      case 'u':
        event.preventDefault();
        formatText('underline');
        break;
    }
  }
};

watch(() => props.modelValue, (newValue) => {
  if (editorRef.value && editorRef.value.innerHTML !== newValue) {
    editorRef.value.innerHTML = newValue;
  }
});

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue;
    editorRef.value.style.maxHeight = props.maxHeight;
  }
});
</script>

<style scoped>
.rich-text-editor {
  border: 2px solid var(--border-light, #e5e7eb);
  border-radius: var(--radius-xl, 12px);
  overflow: hidden;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: all var(--transition-normal, 0.25s ease);
  box-shadow: var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.1));
}

.modern-editor {
  position: relative;
}

.modern-editor::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);
  pointer-events: none;
  z-index: 0;
}

.editor-focused {
  border-color: var(--primary-500, #3b82f6);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.editor-toolbar {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-light, #e5e7eb);
  background: rgba(248, 250, 252, 0.8);
  backdrop-filter: blur(10px);
  gap: 12px;
  position: relative;
  z-index: 1;
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: rgba(255, 255, 255, 0.7);
  border-radius: var(--radius-lg, 8px);
  border: 1px solid rgba(229, 231, 235, 0.5);
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  background: none;
  border-radius: var(--radius-md, 6px);
  cursor: pointer;
  transition: all var(--transition-fast, 0.15s ease);
  font-weight: 600;
  font-size: 14px;
  color: var(--text-secondary, #6b7280);
  position: relative;
}

.toolbar-btn:hover {
  background: var(--primary-50, #f0f9ff);
  color: var(--primary-600, #2563eb);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.1));
}

.toolbar-btn.active {
  background: var(--primary-500, #3b82f6);
  color: white;
  box-shadow: var(--shadow-md, 0 4px 6px rgba(0, 0, 0, 0.1));
}

.toolbar-btn:active {
  transform: translateY(0);
}

.underline-icon {
  font-family: var(--font-sans);
  text-decoration: underline;
  font-weight: bold;
}

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: var(--border-light, #e5e7eb);
  margin: 0 4px;
}

.editor-content {
  min-height: 120px;
  max-height: 300px;
  padding: 16px;
  outline: none;
  line-height: 1.7;
  font-size: var(--text-base, 16px);
  color: var(--text-primary, #1f2937);
  overflow-y: auto;
  position: relative;
  z-index: 1;
  font-family: var(--font-sans);
}

.editor-content:empty:before {
  content: attr(placeholder);
  color: var(--text-muted, #9ca3af);
  pointer-events: none;
  font-style: italic;
}

.editor-content:focus {
  box-shadow: none;
}

.editor-footer {
  padding: 8px 16px;
  border-top: 1px solid var(--border-light, #e5e7eb);
  background: rgba(248, 250, 252, 0.8);
  backdrop-filter: blur(10px);
  display: flex;
  justify-content: flex-end;
  position: relative;
  z-index: 1;
}

.word-count {
  font-size: var(--text-xs, 12px);
  color: var(--text-muted, #9ca3af);
  font-weight: 500;
}

/* 编辑器内容样式 */
.editor-content p {
  margin: 0 0 12px 0;
  line-height: 1.7;
}

.editor-content p:last-child {
  margin-bottom: 0;
}

.editor-content strong {
  font-weight: 700;
  color: var(--text-primary, #1f2937);
}

.editor-content em {
  font-style: italic;
  color: var(--text-secondary, #6b7280);
}

.editor-content u {
  text-decoration: underline;
  text-decoration-color: var(--primary-400, #60a5fa);
  text-underline-offset: 2px;
}

.editor-content ul, .editor-content ol {
  margin: 12px 0;
  padding-left: 24px;
}

.editor-content li {
  margin-bottom: 6px;
  line-height: 1.6;
}

.editor-content a {
  color: var(--primary-600, #2563eb);
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: all var(--transition-fast, 0.15s ease);
  font-weight: 500;
}

.editor-content a:hover {
  color: var(--primary-700, #1d4ed8);
  border-bottom-color: var(--primary-300, #93c5fd);
}

/* 滚动条样式 */
.editor-content::-webkit-scrollbar {
  width: 6px;
}

.editor-content::-webkit-scrollbar-track {
  background: transparent;
}

.editor-content::-webkit-scrollbar-thumb {
  background: var(--gray-300, #d1d5db);
  border-radius: var(--radius-full, 9999px);
  transition: background var(--transition-normal, 0.25s ease);
}

.editor-content::-webkit-scrollbar-thumb:hover {
  background: var(--gray-400, #9ca3af);
}

/* 响应式设计 */
@media (max-width: 768px) {
  .editor-toolbar {
    padding: 8px 12px;
    gap: 8px;
  }
  
  .toolbar-group {
    gap: 2px;
    padding: 2px;
  }
  
  .toolbar-btn {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  
  .editor-content {
    padding: 12px;
    font-size: var(--text-sm, 14px);
  }
  
  .editor-footer {
    padding: 6px 12px;
  }
}

/* 动画效果 */
@keyframes toolbarBtnPress {
  0% { transform: scale(1); }
  50% { transform: scale(0.95); }
  100% { transform: scale(1); }
}

.toolbar-btn:active {
  animation: toolbarBtnPress 0.1s ease;
}
</style> 