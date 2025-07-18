<template>
  <div class="admin-container">
    <!-- 页面标题 -->
    <div class="admin-card mb-6">
      <div class="admin-card__header">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="heading-1">商品批量上传</h1>
            <p class="text-secondary">通过文件夹批量上传商品图片，快速生成Shopify商品数据</p>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div class="stats-card">
              <div class="stats-value">{{ folderGroups.length }}</div>
              <div class="stats-label">待上传商品</div>
            </div>
            <div class="stats-card">
              <div class="stats-value">{{ totalFiles }}</div>
              <div class="stats-label">图片文件</div>
            </div>
            <div class="stats-card">
              <div class="stats-value">{{ products.length }}</div>
              <div class="stats-label">已创建商品</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="grid grid-cols-2 gap-6">
      <!-- 左侧上传区域 -->
      <div class="admin-card">
        <div class="admin-card__header">
          <h2 class="heading-2">批量上传商品文件夹</h2>
        </div>
        
        <div class="admin-card__body">
          <!-- 上传区域 -->
          <div class="upload-area" 
               :class="{ 
                 'disabled': !selectedTemplate, 
                 'has-files': folderGroups.length > 0,
                 'uploading': uploading 
               }" 
               @click="handleFolderSelect">
            
            <div class="upload-icon">
              <el-icon v-if="!uploading"><folder-add /></el-icon>
              <el-icon v-else class="rotating"><loading /></el-icon>
            </div>
            
            <div class="upload-text">
              <template v-if="!selectedTemplate">
                请先在右侧选择模板
              </template>
              <template v-else-if="folderGroups.length === 0">
                点击选择商品文件夹
              </template>
              <template v-else-if="uploading">
                正在上传商品...
              </template>
              <template v-else>
                已选择 {{ folderGroups.length }} 个商品文件夹
              </template>
            </div>
            
            <div class="upload-hint">
              <template v-if="!selectedTemplate">
                支持批量选择多个商品文件夹，每个文件夹内的图片将生成一个商品
              </template>
              <template v-else-if="selectedTemplate && folderGroups.length === 0">
                最大文件大小：5MB | 支持格式：JPG, PNG, WebP<br>
                <span class="text-primary">✓ 已选择模板：{{ currentTemplate?.name }}</span>
              </template>
              <template v-else-if="uploading">
                请稍候，正在处理您的商品文件...
              </template>
              <template v-else>
                共包含 {{ totalFiles }} 个图片文件，点击开始上传
              </template>
            </div>
            
            <input 
              type="file" 
              ref="folderInput" 
              webkitdirectory 
              multiple 
              style="display: none" 
              @change="handleFolderChange"
              accept="image/*"
            >
          </div>
          
          <!-- 文件夹预览 -->
          <div v-if="folderGroups.length > 0" class="mt-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="heading-3">商品预览</h3>
              <div class="admin-tag admin-tag--primary">{{ folderGroups.length }} 个商品</div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div v-for="(group, index) in folderGroups" 
                   :key="index" 
                   class="admin-card">
                <div class="flex justify-between items-start p-4">
                  <div>
                    <div class="text-primary font-medium">{{ group.folderName }}</div>
                    <div class="text-secondary text-sm">{{ group.files.length }} 张图片</div>
                  </div>
                  <button 
                    class="admin-btn admin-btn--danger"
                    @click="removeFolderGroup(index)"
                  >
                    <el-icon><close /></el-icon>
                  </button>
                </div>
                
                <div class="px-4 pb-4">
                  <div class="flex flex-wrap gap-2">
                    <div v-for="file in group.files.slice(0, 3)" 
                         :key="file.name" 
                         class="admin-tag">
                      {{ file.name.length > 12 ? file.name.substring(0, 12) + '...' : file.name }}
                    </div>
                    <div v-if="group.files.length > 3" 
                         class="admin-tag">
                      +{{ group.files.length - 3 }} 更多
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 操作按钮 -->
        <div v-if="folderGroups.length > 0" class="admin-card__footer">
          <button 
            class="admin-btn admin-btn--primary"
            @click="submitFolderUpload"
            :disabled="!selectedTemplate"
          >
            <el-icon><upload /></el-icon>
            {{ uploading ? '上传中...' : `开始上传 ${folderGroups.length} 个商品` }}
          </button>
          <button 
            class="admin-btn ml-4"
            @click="clearFolders" 
            :disabled="uploading"
          >
            <el-icon><delete /></el-icon>
            清空列表
          </button>
        </div>
      </div>
      
      <!-- 右侧模板选择区域 -->
      <div class="admin-card">
        <div class="admin-card__header">
          <div class="flex justify-between items-center">
            <h2 class="heading-2">选择商品模板</h2>
            <button 
              class="admin-btn admin-btn--primary"
              @click="createTemplate"
            >
              <el-icon><plus /></el-icon>
              新建模板
            </button>
          </div>
        </div>
        
        <div class="admin-card__body">
          <!-- 模板选择器 -->
          <div class="mb-6">
            <select 
              v-model="selectedTemplate" 
              class="admin-select"
              @change="onTemplateChange"
            >
              <option value="">请选择商品模板</option>
              <option v-for="item in templates"
                      :key="item.id"
                      :value="item.id">
                {{ item.name }} ({{ item.type || '无类型' }})
              </option>
            </select>
          </div>
          
          <!-- 模板预览 -->
          <div v-if="currentTemplate">
            <div class="flex justify-between items-center mb-4">
              <h3 class="heading-3">模板详情</h3>
              <div class="admin-tag" 
                   :class="currentTemplate.status === 'active' ? 'admin-tag--success' : ''">
                {{ currentTemplate.status === 'active' ? '启用中' : '草稿' }}
              </div>
            </div>
            
            <div class="admin-card bg-hover p-6">
              <div class="mb-4">
                <div class="text-xl font-semibold text-primary">{{ currentTemplate.name }}</div>
                <div class="text-secondary mt-2">格式：{{ currentTemplate.title_format }}</div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-secondary mb-1">供应商</div>
                  <div class="text-primary">{{ currentTemplate.vendor || '未设置' }}</div>
                </div>
                <div>
                  <div class="text-secondary mb-1">商品类型</div>
                  <div class="text-primary">{{ currentTemplate.type || '未设置' }}</div>
                </div>
                <div>
                  <div class="text-secondary mb-1">默认价格</div>
                  <div class="text-primary">¥{{ currentTemplate.price || '0.00' }}</div>
                </div>
                <div v-if="currentTemplate.compare_at_price">
                  <div class="text-secondary mb-1">对比价格</div>
                  <div class="text-primary">¥{{ currentTemplate.compare_at_price }}</div>
                </div>
                <div v-if="currentTemplate.collection">
                  <div class="text-secondary mb-1">所属集合</div>
                  <div class="text-primary">{{ currentTemplate.collection }}</div>
                </div>
                <div v-if="currentTemplate.variant_inventory_qty">
                  <div class="text-secondary mb-1">默认库存</div>
                  <div class="text-primary">{{ currentTemplate.variant_inventory_qty }}</div>
                </div>
              </div>
              
              <!-- 选项预览 -->
              <div v-if="currentTemplate.option1_name" class="mt-6">
                <div class="text-secondary mb-2">商品选项</div>
                <div class="flex flex-wrap gap-2">
                  <div v-if="currentTemplate.option1_name" class="admin-tag">
                    {{ currentTemplate.option1_name }}: {{ currentTemplate.option1_value }}
                  </div>
                  <div v-if="currentTemplate.option2_name" class="admin-tag">
                    {{ currentTemplate.option2_name }}: {{ currentTemplate.option2_value }}
                  </div>
                  <div v-if="currentTemplate.option3_name" class="admin-tag">
                    {{ currentTemplate.option3_name }}: {{ currentTemplate.option3_value }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- 空状态 -->
          <div v-else class="text-center py-12">
            <div class="upload-icon">
              <el-icon><document /></el-icon>
            </div>
            <div class="text-lg font-medium text-primary mt-4">请选择一个模板开始上传</div>
            <div class="text-secondary mt-2">模板包含商品的基本信息设置，如价格、描述、选项等</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 商品列表 -->
    <div class="admin-card mt-6">
      <div class="admin-card__header">
        <div class="flex justify-between items-center">
          <h2 class="heading-2">商品管理</h2>
          <button 
            class="admin-btn admin-btn--primary"
            @click="exportCsv"
          >
            <el-icon><download /></el-icon>
            导出CSV
          </button>
        </div>
      </div>
      
      <div v-if="products.length === 0" class="text-center py-12">
        <div class="upload-icon">
          <el-icon><box /></el-icon>
        </div>
        <div class="text-lg font-medium text-primary mt-4">暂无商品</div>
        <div class="text-secondary mt-2">开始上传您的第一个商品文件夹吧！</div>
      </div>
      
      <div v-else class="admin-card__body">
        <table class="admin-table">
          <thead>
            <tr>
              <th>商品名称</th>
              <th>供应商</th>
              <th>类型</th>
              <th class="text-right">价格</th>
              <th class="text-center">状态</th>
              <th class="text-center">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in products" :key="item.id">
              <td>
                <div class="flex items-center gap-2">
                  <div class="text-primary">{{ item.title }}</div>
                  <div v-if="item.handle" class="admin-tag">
                    {{ item.handle }}
                  </div>
                </div>
              </td>
              <td>{{ item.vendor }}</td>
              <td>{{ item.type }}</td>
              <td class="text-right">¥{{ item.variant_price || '0.00' }}</td>
              <td class="text-center">
                <div class="admin-tag" 
                     :class="item.status === 'active' ? 'admin-tag--success' : ''">
                  {{ item.status === 'active' ? '启用' : '草稿' }}
                </div>
              </td>
              <td class="text-center">
                <button 
                  class="admin-btn admin-btn--primary"
                  @click="editProduct(item)"
                >
                  <el-icon><edit /></el-icon>
                  编辑
                </button>
                <button 
                  class="admin-btn admin-btn--danger ml-2"
                  @click="deleteProduct(item)"
                >
                  <el-icon><delete /></el-icon>
                  删除
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 编辑对话框 -->
    <el-dialog 
      v-model="editDialogVisible" 
      custom-class="admin-dialog"
      title="编辑商品信息" 
      width="800px"
      :close-on-click-modal="false"
    >
      <div class="admin-dialog__body">
        <form class="space-y-6">
          <div class="space-y-4">
            <label class="text-secondary">商品名称</label>
            <input 
              v-model="editForm.title" 
              class="admin-input"
              placeholder="请输入商品名称"
            >
          </div>
          
          <div class="space-y-4">
            <label class="text-secondary">商品描述</label>
            <textarea 
              v-model="editForm.body_html" 
              class="admin-input"
              rows="4"
              placeholder="请输入商品描述"
            ></textarea>
          </div>
          
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
              <label class="text-secondary">供应商</label>
              <input 
                v-model="editForm.vendor" 
                class="admin-input"
                placeholder="请输入供应商"
              >
            </div>
            
            <div class="space-y-4">
              <label class="text-secondary">商品类型</label>
              <input 
                v-model="editForm.type" 
                class="admin-input"
                placeholder="请输入商品类型"
              >
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
              <label class="text-secondary">销售价格</label>
              <input 
                v-model="editForm.variant_price" 
                type="number" 
                step="0.01"
                class="admin-input"
                placeholder="请输入销售价格"
              >
            </div>
            
            <div class="space-y-4">
              <label class="text-secondary">商品状态</label>
              <select 
                v-model="editForm.status" 
                class="admin-select"
              >
                <option value="active">启用</option>
                <option value="draft">草稿</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      
      <div class="admin-dialog__footer">
        <button 
          class="admin-btn"
          @click="editDialogVisible = false"
        >
          取消
        </button>
        <button 
          class="admin-btn admin-btn--primary ml-4"
          @click="saveProduct"
        >
          保存更改
        </button>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { UploadFilled, FolderAdd, Loading, Upload, Delete, Close, Document, Plus, Goods, Download, Box, Edit } from '@element-plus/icons-vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const templates = ref([]);
const selectedTemplate = ref(null);
const products = ref([]);
const fileList = ref([]);
const folderGroups = ref([]); // 文件夹分组数组
const folderInput = ref(null); // 文件夹选择输入框引用
const uploading = ref(false); // 上传状态
const uploadRef = ref(null);
const editDialogVisible = ref(false);
const editForm = ref({});

// 旧的上传计算属性已移除，现在使用文件夹分组上传

const currentTemplate = computed(() => {
  if (!templates.value || !Array.isArray(templates.value)) {
    console.warn('templates.value 不是数组:', templates.value);
    return null;
  }
  if (!selectedTemplate.value) {
    return null;
  }
  const found = templates.value.find(t => t.id === selectedTemplate.value);
  console.log('找到的模板:', found);
  return found;
});

// 计算总文件数
const totalFiles = computed(() => {
  return folderGroups.value.reduce((total, group) => total + group.files.length, 0);
});

// 获取模板列表
const fetchTemplates = async () => {
  try {
    console.log('正在获取模板列表...');
    const response = await axios.get('/api/templates');
    console.log('获取到的响应:', response);

    if (response.data.status === 'success' && Array.isArray(response.data.data)) {
      console.log('使用新格式数据（status+data）');
      templates.value = response.data.data;
    } else if (Array.isArray(response.data)) {
      console.log('使用旧格式数据（数组）');
      templates.value = response.data;
    } else {
      console.error('获取到的数据格式不正确:', response.data);
      ElMessage.error('获取模板列表失败：数据格式不正确');
      templates.value = [];
    }
    console.log('设置后的templates.value:', templates.value);
  } catch (error) {
    console.error('获取模板列表失败:', error);
    ElMessage.error('获取模板列表失败');
    templates.value = [];
  }
};

// 监听模板选择变化
watch(selectedTemplate, (newVal, oldVal) => {
  console.log('选择的模板ID变化:', { old: oldVal, new: newVal });
  if (newVal) {
    console.log('当前模板列表:', templates.value);
    console.log('当前选中模板:', currentTemplate.value);
  }
});

// 模板选择变化处理
const onTemplateChange = (templateId) => {
  console.log('模板选择变化:', templateId);
  if (templateId) {
    ElMessage.success('模板选择成功，现在可以选择商品文件夹了！');
  }
};

// 处理文件夹选择
const handleFolderSelect = () => {
  if (!selectedTemplate.value) {
    ElMessage.warning('请先选择模板');
    return;
  }
  folderInput.value.click();
};

// 处理文件夹变化
const handleFolderChange = (event) => {
  const files = Array.from(event.target.files);
  console.log('选择的文件:', files);
  
  if (files.length === 0) return;
  
  // 按文件夹路径分组
  const groupedFiles = {};
  
  files.forEach(file => {
    // 提取文件夹名称（取路径中的第一级目录名）
    const pathParts = file.webkitRelativePath.split('/');
    const folderName = pathParts[0];
    
    // 检查是否为图片文件
    const isImage = /\.(jpg|jpeg|png|webp)$/i.test(file.name);
    if (!isImage) return;
    
    // 检查文件大小
    const isLt5M = file.size / 1024 / 1024 < 5;
    if (!isLt5M) {
      ElMessage.warning(`文件 ${file.name} 超过5MB，已跳过`);
      return;
    }
    
    if (!groupedFiles[folderName]) {
      groupedFiles[folderName] = [];
    }
    groupedFiles[folderName].push(file);
  });
  
  // 转换为数组格式
  const newGroups = Object.entries(groupedFiles).map(([folderName, files]) => ({
    folderName,
    files
  }));
  
  // 添加到现有分组中（支持多次选择）
  folderGroups.value.push(...newGroups);
  
  console.log('文件夹分组:', folderGroups.value);
  ElMessage.success(`成功添加 ${newGroups.length} 个商品文件夹，共 ${files.filter(f => /\.(jpg|jpeg|png|webp)$/i.test(f.name)).length} 个图片文件`);
  
  // 清空输入框以支持重复选择
  event.target.value = '';
};

// 删除文件夹分组
const removeFolderGroup = (index) => {
  folderGroups.value.splice(index, 1);
  ElMessage.info('已删除商品文件夹');
};

// 在组件挂载时获取数据
onMounted(() => {
  console.log('Home组件已挂载');
  fetchTemplates();
  fetchProducts();
});

// 这些旧的上传方法已不再需要，已被新的文件夹上传逻辑替代

// 提交文件夹上传
const submitFolderUpload = async () => {
  if (!selectedTemplate.value) {
    ElMessage.warning('请先选择模板');
    return;
  }
  if (folderGroups.value.length === 0) {
    ElMessage.warning('请先选择商品文件夹');
    return;
  }
  
  uploading.value = true;
  let successCount = 0;
  let errorCount = 0;
  
  try {
    console.log('开始批量上传文件夹，商品数量:', folderGroups.value.length);
    
    for (const group of folderGroups.value) {
      try {
        console.log(`正在上传商品: ${group.folderName}，图片数量: ${group.files.length}`);
        
        const formData = new FormData();
        formData.append('template_id', selectedTemplate.value);
        formData.append('folder_name', group.folderName);
        
        // 添加所有文件
        group.files.forEach((file, index) => {
          formData.append(`files[${index}]`, file);
        });
        
        const response = await axios.post('/api/upload-folder', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        console.log(`商品 ${group.folderName} 上传成功:`, response.data);
        successCount++;
        
      } catch (error) {
        console.error(`商品 ${group.folderName} 上传失败:`, error);
        errorCount++;
        ElMessage.error(`商品 "${group.folderName}" 上传失败: ${error.response?.data?.message || error.message}`);
      }
    }
    
    // 显示总结信息
    if (successCount > 0) {
      ElMessage.success(`成功上传 ${successCount} 个商品${errorCount > 0 ? `，失败 ${errorCount} 个` : ''}`);
      // 刷新商品列表
      fetchProducts();
      // 清空文件夹列表
      clearFolders();
    } else {
      ElMessage.error('所有商品上传都失败了');
    }
    
  } catch (error) {
    console.error('批量上传过程中发生错误:', error);
    ElMessage.error('批量上传失败');
  } finally {
    uploading.value = false;
  }
};

// 清空文件夹列表
const clearFolders = () => {
  folderGroups.value = [];
  // 重置文件输入框
  if (folderInput.value) {
    folderInput.value.value = '';
  }
  ElMessage.info('已清空文件夹列表');
};

// 旧的Element Plus上传处理方法已全部移除

// 创建模板
const createTemplate = () => {
  router.push('/templates');
};

const editProduct = (product) => {
  editForm.value = { ...product };
  editDialogVisible.value = true;
};

const saveProduct = async () => {
  try {
    await axios.put(`/api/products/${editForm.value.id}`, editForm.value);
    ElMessage.success('商品更新成功');
    editDialogVisible.value = false;
    fetchProducts();
  } catch (error) {
    ElMessage.error('更新失败');
  }
};

const deleteProduct = async (product) => {
  try {
    await ElMessageBox.confirm('确定要删除这个商品吗？');
    await axios.delete(`/api/products/${product.id}`);
    ElMessage.success('商品删除成功');
    fetchProducts();
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败');
    }
  }
};

const exportCsv = async () => {
  try {
    const response = await axios.get('/api/products/export', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `products-${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    ElMessage.error('导出失败');
  }
};

const router = useRouter();

// 获取商品列表
const fetchProducts = async () => {
  try {
    console.log('正在获取商品列表...');
    const response = await axios.get('/api/products');
    console.log('获取到的商品列表:', response.data);
    
    if (response.data.status === 'success' && Array.isArray(response.data.data)) {
      products.value = response.data.data;
    } else if (Array.isArray(response.data)) {
      products.value = response.data;
    } else {
      console.error('获取到的商品数据格式不正确:', response.data);
      ElMessage.error('获取商品列表失败：数据格式不正确');
      products.value = [];
    }
  } catch (error) {
    console.error('获取商品列表失败:', error);
    ElMessage.error('获取商品列表失败');
    products.value = [];
  }
};
</script>

<style>
.admin-container {
  min-height: 100vh;
  padding: 24px;
  background-color: var(--bg-body);
}

.admin-card {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.admin-card__footer {
  padding: 1.5rem;
  border-top: 1px solid var(--glass-border);
  background: rgba(99, 102, 241, 0.05);
}

.rotating {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style> 