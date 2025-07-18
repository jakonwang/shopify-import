<template>
  <div class="templates-container">
    <!-- 现代化背景装饰 -->
    <div class="bg-decoration">
      <div class="decoration-shape shape-1"></div>
      <div class="decoration-shape shape-2"></div>
      <div class="decoration-shape shape-3"></div>
    </div>

    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <div class="title-icon-wrapper">
              <el-icon class="title-icon"><Document /></el-icon>
            </div>
            <div class="title-text">
              <span class="title-main">模板管理</span>
              <span class="title-subtitle">创建和管理Shopify商品模板</span>
            </div>
          </h1>
        </div>
        <div class="header-actions">
          <el-button 
            type="primary" 
            @click="showCreateDialog" 
            size="large" 
            class="create-btn"
          >
            <el-icon><Plus /></el-icon>
            新建模板
          </el-button>
        </div>
      </div>
    </div>

    <!-- 模板卡片网格 -->
    <div class="templates-grid">
      <div 
        v-for="template in templates" 
        :key="template.id" 
        class="template-card glass-card"
      >
        <div class="card-header">
          <div class="template-info">
            <h3 class="template-name">{{ template.name }}</h3>
            <div class="template-meta">
              <span class="vendor">{{ template.vendor || '无供应商' }}</span>
              <span class="separator">•</span>
              <span class="type">{{ template.type || '无类型' }}</span>
            </div>
          </div>
          <el-tag 
            :type="template.status === 'active' ? 'success' : 'info'" 
            class="status-tag"
            effect="light"
          >
            {{ template.status === 'active' ? '启用' : '草稿' }}
          </el-tag>
        </div>
        
        <div class="card-content">
          <div class="template-details">
            <div class="detail-item">
              <div class="detail-label">
                <el-icon><edit /></el-icon>
                标题格式
              </div>
              <div class="detail-value">{{ template.title_format }}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">
                <el-icon><money /></el-icon>
                默认价格
              </div>
              <div class="detail-value price">¥{{ template.price || '0.00' }}</div>
            </div>
            <div class="detail-item" v-if="template.collection">
              <div class="detail-label">
                <el-icon><collection /></el-icon>
                集合
              </div>
              <div class="detail-value">{{ template.collection }}</div>
            </div>
          </div>
        </div>
        
        <div class="card-actions">
          <el-button 
            type="primary"
            link 
            @click="editTemplate(template)"
            class="action-btn edit-btn"
          >
            <el-icon><Edit /></el-icon>
            编辑
          </el-button>
          <el-button 
            type="danger"
            link 
            @click="deleteTemplate(template)"
            class="action-btn delete-btn"
          >
            <el-icon><Delete /></el-icon>
            删除
          </el-button>
        </div>
      </div>
      
      <!-- 空状态 -->
      <div v-if="templates.length === 0" class="empty-state glass-card">
        <div class="empty-content">
          <div class="empty-icon-wrapper">
            <el-icon class="empty-icon"><DocumentAdd /></el-icon>
          </div>
          <h3 class="empty-title">还没有模板</h3>
          <p class="empty-description">创建您的第一个模板来开始批量上传商品吧！</p>
          <el-button 
            type="primary" 
            @click="showCreateDialog" 
            class="create-first-btn"
            size="large"
          >
            <el-icon><Plus /></el-icon>
            创建第一个模板
          </el-button>
        </div>
      </div>
    </div>

    <!-- 现代化创建/编辑模板对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑模板' : '新建模板'"
      width="80%"
      class="template-dialog modern-dialog"
      :close-on-click-modal="false"
      top="5vh"
    >
      <div class="dialog-content">
        <el-form 
          :model="form" 
          :rules="rules" 
          ref="formRef" 
          label-position="top" 
          class="template-form"
        >
          <!-- 基本信息 -->
          <div class="form-section">
            <h4 class="section-title">
              <div class="section-icon">
                <el-icon><InfoFilled /></el-icon>
              </div>
              基本信息
            </h4>
            <div class="form-grid">
              <el-form-item label="模板名称" prop="name" class="form-item">
                <el-input 
                  v-model="form.name" 
                  placeholder="请输入模板名称" 
                  size="large"
                />
              </el-form-item>
              <el-form-item label="状态" prop="status" class="form-item">
                <el-select 
                  v-model="form.status" 
                  placeholder="请选择状态"
                  size="large"
                >
                  <el-option label="启用" value="active" />
                  <el-option label="草稿" value="draft" />
                </el-select>
              </el-form-item>
              <el-form-item label="供应商" prop="vendor" class="form-item">
                <el-input 
                  v-model="form.vendor" 
                  placeholder="请输入供应商名称" 
                  size="large"
                />
              </el-form-item>
              <el-form-item label="商品类型" prop="type" class="form-item">
                <el-input 
                  v-model="form.type" 
                  placeholder="请输入商品类型" 
                  size="large"
                />
              </el-form-item>
            </div>
            
            <el-form-item label="标题格式" prop="title_format" class="form-item-full">
              <el-input 
                v-model="form.title_format" 
                placeholder="请输入标题格式" 
                size="large"
              />
              <div class="form-tip">
                <el-icon><QuestionFilled /></el-icon>
                可用变量：{folder_name}, {prefix}, {suffix}
              </div>
            </el-form-item>
            
            <el-form-item label="商品描述" prop="body_html" class="form-item-full">
              <RichTextEditor
                v-model="form.body_html"
                placeholder="请输入商品描述，支持富文本格式"
              />
            </el-form-item>
            
            <el-form-item label="集合" prop="collection" class="form-item-full">
              <el-input 
                v-model="form.collection" 
                placeholder="请输入商品集合名称" 
                size="large"
              />
            </el-form-item>
          </div>

          <!-- 价格信息 -->
          <div class="form-section">
            <h4 class="section-title">
              <div class="section-icon">
                <el-icon><Money /></el-icon>
              </div>
              价格信息
            </h4>
            <div class="form-grid">
              <el-form-item label="默认价格" prop="price" class="form-item">
                <el-input-number
                  v-model="form.price"
                  :precision="2"
                  :step="0.01"
                  :min="0"
                  placeholder="请输入默认价格"
                  size="large"
                  style="width: 100%"
                />
              </el-form-item>
              <el-form-item label="对比价格" prop="compare_at_price" class="form-item">
                <el-input-number
                  v-model="form.compare_at_price"
                  :precision="2"
                  :step="0.01"
                  :min="0"
                  placeholder="请输入对比价格"
                  size="large"
                  style="width: 100%"
                />
              </el-form-item>
              <el-form-item label="成本价格" prop="cost_per_item" class="form-item">
                <el-input-number
                  v-model="form.cost_per_item"
                  :precision="2"
                  :step="0.01"
                  :min="0"
                  placeholder="请输入成本价格"
                  size="large"
                  style="width: 100%"
                />
              </el-form-item>
            </div>
          </div>

          <!-- 高级选项（可选） -->
          <div class="form-section advanced-section">
            <h4 class="section-title">
              <div class="section-icon">
                <el-icon><Setting /></el-icon>
              </div>
              产品变体设置
              <el-tag size="small" type="info" style="margin-left: 8px;">高级功能</el-tag>
            </h4>
            
            <!-- 产品选项配置 -->
            <div class="product-options-config">
              <div class="config-header">
                <h5>产品选项配置</h5>
                <p>定义产品的可变属性，如尺寸、颜色、材质等</p>
              </div>
              
              <div class="options-grid">
                <div class="option-group" v-for="(option, index) in productOptions" :key="index">
                  <div class="option-header">
                    <span class="option-label">选项 {{ index + 1 }}</span>
                    <el-button 
                      v-if="productOptions.length > 1" 
                      link 
                      type="danger" 
                      size="small"
                      @click="removeOption(index)"
                    >
                      <el-icon><close /></el-icon>
                    </el-button>
                  </div>
                  <el-form-item :label="`选项名称`" class="option-name">
                    <el-input 
                      v-model="option.name" 
                      placeholder="如：尺寸、颜色、材质"
                      @input="updateVariants"
                    />
                  </el-form-item>
                  <el-form-item :label="`选项值`" class="option-values">
                    <el-select
                      v-model="option.values"
                      multiple
                      filterable
                      allow-create
                      placeholder="输入选项值，按回车添加"
                      @change="updateVariants"
                      style="width: 100%"
                    >
                      <el-option
                        v-for="value in option.values"
                        :key="value"
                        :label="value"
                        :value="value"
                      />
                    </el-select>
                    <div class="values-hint">
                      例如：S, M, L, XL 或 红色, 蓝色, 绿色
                    </div>
                  </el-form-item>
                </div>
              </div>
              
              <div class="option-actions">
                <el-button 
                  v-if="productOptions.length < 3" 
                  type="primary" 
                  plain 
                  @click="addOption"
                >
                  <el-icon><plus /></el-icon>
                  添加选项
                </el-button>
                <span class="option-limit-hint">最多支持3个产品选项</span>
              </div>
            </div>

            <!-- 变体预览和管理 -->
            <div v-if="generatedVariants.length > 0" class="variants-management">
              <div class="variants-header">
                <h5>产品变体管理</h5>
                <p>为每个变体设置具体的价格、库存等信息（共 {{ generatedVariants.length }} 个变体）</p>
              </div>
              
              <div class="variants-table-container">
                <el-table :data="generatedVariants" class="variants-table" size="small">
                  <el-table-column label="变体" min-width="200">
                    <template #default="scope">
                      <div class="variant-name">
                        <el-tag 
                          v-for="(value, index) in scope.row.options" 
                          :key="index" 
                          size="small" 
                          class="variant-tag"
                        >
                          {{ value }}
                        </el-tag>
                      </div>
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="价格" width="120">
                    <template #default="scope">
                      <el-input-number
                        v-model="scope.row.price"
                        :precision="2"
                        :min="0"
                        :step="0.01"
                        size="small"
                        style="width: 100%"
                      />
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="对比价格" width="120">
                    <template #default="scope">
                      <el-input-number
                        v-model="scope.row.compare_at_price"
                        :precision="2"
                        :min="0"
                        :step="0.01"
                        size="small"
                        style="width: 100%"
                      />
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="库存" width="100">
                    <template #default="scope">
                      <el-input-number
                        v-model="scope.row.inventory_qty"
                        :min="0"
                        size="small"
                        style="width: 100%"
                      />
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="SKU" width="150">
                    <template #default="scope">
                      <el-input
                        v-model="scope.row.sku"
                        placeholder="SKU编码"
                        size="small"
                      />
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="重量(g)" width="100">
                    <template #default="scope">
                      <el-input-number
                        v-model="scope.row.grams"
                        :min="0"
                        size="small"
                        style="width: 100%"
                      />
                    </template>
                  </el-table-column>
                  
                  <el-table-column label="条形码" width="150">
                    <template #default="scope">
                      <el-input
                        v-model="scope.row.barcode"
                        placeholder="条形码"
                        size="small"
                      />
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              
              <div class="variants-actions">
                <el-button @click="resetVariantsPrices">
                  统一价格
                </el-button>
                <el-button @click="autoGenerateSkus">
                  自动生成SKU
                </el-button>
                <el-button @click="clearAllVariants" type="danger" plain>
                  清空变体
                </el-button>
              </div>
            </div>

            <!-- 简化模式提示 -->
            <div v-else class="simple-mode-hint">
              <el-icon><info-filled /></el-icon>
              <div class="hint-content">
                <h6>简化模式</h6>
                <p>当前为简化模式，将使用文件夹名作为单一商品名称。如需使用多变体，请在上方配置产品选项。</p>
              </div>
            </div>
          </div>

          <!-- SEO设置 -->
          <div class="form-section">
            <h4 class="section-title">
              <div class="section-icon">
                <el-icon><Search /></el-icon>
              </div>
              SEO设置
            </h4>
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="SEO标题">
                  <el-input v-model="form.seo_title" placeholder="SEO页面标题" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Google类别">
                  <el-input v-model="form.google_shopping_category" placeholder="Google购物类别" />
                </el-form-item>
              </el-col>
            </el-row>
            <el-form-item label="SEO描述">
              <RichTextEditor
                v-model="form.seo_description"
                placeholder="请输入SEO页面描述"
              />
            </el-form-item>
          </div>

          <!-- 库存设置 -->
          <div class="form-section">
            <h4 class="section-title">
              <div class="section-icon">
                <el-icon><Box /></el-icon>
              </div>
              库存设置
            </h4>
            <el-row :gutter="20">
              <el-col :span="8">
                <el-form-item label="库存数量">
                  <el-input-number
                    v-model="form.variant_inventory_qty"
                    :min="0"
                    placeholder="库存数量"
                    size="large"
                    style="width: 100%"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="重量(克)">
                  <el-input-number
                    v-model="form.variant_grams"
                    :min="0"
                    placeholder="商品重量"
                    size="large"
                    style="width: 100%"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="条形码">
                  <el-input v-model="form.variant_barcode" placeholder="商品条形码" />
                </el-form-item>
              </el-col>
            </el-row>
            
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="需要运费">
                  <el-switch v-model="form.variant_requires_shipping" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="需要缴税">
                  <el-switch v-model="form.variant_taxable" />
                </el-form-item>
              </el-col>
            </el-row>
          </div>
        </el-form>
      </div>
      
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">取消</el-button>
          <el-button type="primary" @click="saveTemplate" size="large" :loading="saving">
            {{ saving ? '保存中...' : '确定保存' }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  Plus, Edit, Delete, Document, DocumentAdd, InfoFilled, 
  QuestionFilled, Money, Setting, Search, Box, Grid, Collection, Edit as edit, Money as money, Collection as collection,
  Close, Plus as plus, InfoFilled as infoFilled
} from '@element-plus/icons-vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import RichTextEditor from '../components/RichTextEditor.vue';

const templates = ref([]);
const dialogVisible = ref(false);
const isEdit = ref(false);
const formRef = ref(null);
const saving = ref(false);
const advancedCollapse = ref([]);

// 产品变体相关数据
const productOptions = ref([
  { name: '', values: [] }
]);
const generatedVariants = ref([]);

const form = ref({
  name: '',
  vendor: '',
  type: '',
  title_format: '',
  body_html: '',
  price: 0,
  compare_at_price: 0,
  cost_per_item: 0,
  status: 'active',
  collection: '',
  
  // SEO字段
  seo_title: '',
  seo_description: '',
  
  // Google Shopping字段
  google_shopping_category: '',
  google_shopping_gender: '',
  google_shopping_age_group: '',
  google_shopping_mpn: '',
  google_shopping_adwords_grouping: '',
  google_shopping_adwords_labels: '',
  google_shopping_condition: 'new',
  google_shopping_custom_product: false,
  google_shopping_custom_label_0: '',
  google_shopping_custom_label_1: '',
  google_shopping_custom_label_2: '',
  google_shopping_custom_label_3: '',
  google_shopping_custom_label_4: '',
  
  // 产品选项和变体将通过productOptions和generatedVariants管理
  product_options: [],
  variants: []
});

const rules = ref({
  name: [
    { required: true, message: '请输入模板名称', trigger: 'blur' },
    { min: 2, max: 255, message: '长度在 2 到 255 个字符', trigger: 'blur' }
  ],
  title_format: [
    { required: true, message: '请输入标题格式', trigger: 'blur' },
    { min: 2, max: 255, message: '长度在 2 到 255 个字符', trigger: 'blur' }
  ],
  price: [
    { type: 'number', min: 0, message: '价格不能小于0', trigger: 'change' }
  ],
  compare_at_price: [
    { type: 'number', min: 0, message: '对比价格不能小于0', trigger: 'change' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' },
    { type: 'enum', enum: ['active', 'draft'], message: '状态必须是启用或草稿', trigger: 'change' }
  ]
});

// 显示创建对话框
const showCreateDialog = () => {
  isEdit.value = false;
  
  // 重置表单数据
  form.value = {
    name: '',
    vendor: '',
    type: '',
    title_format: '',
    body_html: '',
    price: 0,
    compare_at_price: 0,
    cost_per_item: 0,
    status: 'active',
    collection: '',
    seo_title: '',
    seo_description: '',
    google_shopping_category: '',
    google_shopping_gender: '',
    google_shopping_age_group: '',
    google_shopping_mpn: '',
    google_shopping_adwords_grouping: '',
    google_shopping_adwords_labels: '',
    google_shopping_condition: 'new',
    google_shopping_custom_product: false,
    google_shopping_custom_label_0: '',
    google_shopping_custom_label_1: '',
    google_shopping_custom_label_2: '',
    google_shopping_custom_label_3: '',
    google_shopping_custom_label_4: '',
    product_options: [],
    variants: []
  };
  
  // 重置变体数据
  productOptions.value = [{ name: '', values: [] }];
  generatedVariants.value = [];
  
  dialogVisible.value = true;
  if (formRef.value) {
    formRef.value.resetFields();
  }
};

// 显示编辑对话框
const editTemplate = (template) => {
  isEdit.value = true;
  form.value = { ...template };
  
  // 恢复变体数据
  if (template.product_options && Array.isArray(template.product_options)) {
    productOptions.value = template.product_options.map(option => ({
      name: option.name || '',
      values: option.values || []
    }));
  } else {
    // 兼容旧格式
    productOptions.value = [];
    if (template.option1_name) {
      productOptions.value.push({
        name: template.option1_name,
        values: template.option1_value ? [template.option1_value] : []
      });
    }
    if (template.option2_name) {
      productOptions.value.push({
        name: template.option2_name,
        values: template.option2_value ? [template.option2_value] : []
      });
    }
    if (template.option3_name) {
      productOptions.value.push({
        name: template.option3_name,
        values: template.option3_value ? [template.option3_value] : []
      });
    }
    
    if (productOptions.value.length === 0) {
      productOptions.value = [{ name: '', values: [] }];
    }
  }
  
  // 恢复变体数据
  if (template.variants && Array.isArray(template.variants)) {
    generatedVariants.value = template.variants;
  } else {
    generatedVariants.value = [];
  }
  
  // 如果有选项但没有变体，重新生成变体
  if (productOptions.value.some(opt => opt.name && opt.values.length > 0) && generatedVariants.value.length === 0) {
    updateVariants();
  }
  
  dialogVisible.value = true;
  if (formRef.value) {
    formRef.value.resetFields();
  }
};

// 获取模板列表
const fetchTemplates = async () => {
  try {
    console.log('正在获取模板列表...');
    const response = await axios.get('/api/templates');
    console.log('获取到的完整响应:', {
      status: response.status,
      headers: response.headers,
      data: response.data
    });

    templates.value = response.data;
    
    if (!response.data) {
      throw new Error('响应数据为空');
    }

    if (Array.isArray(response.data)) {
      console.log('使用旧格式数据（数组）');
      templates.value = response.data;
      return;
    }

    if (response.data.status === 'success' && Array.isArray(response.data.data)) {
      console.log('使用新格式数据（status+data）');
      templates.value = response.data.data;
      return;
    }

    console.error('数据格式不符合预期:', response.data);
    ElMessage.warning('数据格式不符合预期，但仍然尝试显示');

  } catch (error) {
    console.error('获取模板列表失败:', {
      error: error,
      message: error.message,
      response: error.response,
      stack: error.stack
    });

    if (error.response?.data?.message) {
      ElMessage.error(error.response.data.message);
    } else {
      ElMessage.error(`获取模板列表失败：${error.message || '未知错误'}`);
    }

    templates.value = [];
  }
};

// 保存模板
const saveTemplate = async () => {
  if (!formRef.value) return;
  
  try {
    await formRef.value.validate();
    saving.value = true;
    
    // 使用新的数据准备方法
    const formData = prepareFormDataForSaving();
    console.log('正在保存模板:', formData);
    
    if (isEdit.value) {
      const response = await axios.put(`/api/templates/${formData.id}`, formData);
      console.log('更新模板响应:', response.data);
      ElMessage.success('模板更新成功');
    } else {
      console.log('发送创建模板请求...');
      const response = await axios.post('/api/templates', formData);
      console.log('创建模板响应:', response);
      
      if (response.status === 201 && response.data) {
        console.log('模板创建成功:', response.data);
        ElMessage.success('模板创建成功');
        dialogVisible.value = false;
        await fetchTemplates();
      } else {
        console.error('创建模板响应异常:', response);
        ElMessage.error('创建模板失败：响应格式不正确');
      }
    }
    
    if (isEdit.value) {
      dialogVisible.value = false;
      await fetchTemplates();
    }
  } catch (error) {
    console.error('保存模板时出错:', error);
    console.error('错误详情:', {
      response: error.response,
      message: error.message,
      stack: error.stack
    });
    
    if (error.response?.data?.message) {
      ElMessage.error(`保存模板失败: ${error.response.data.message}`);
    } else if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat();
      ElMessage.error(`保存模板失败: ${errorMessages.join(', ')}`);
    } else if (error.name === 'ValidationError') {
      return;
    } else {
      ElMessage.error(`保存模板失败：${error.message || '未知错误'}`);
    }
  } finally {
    saving.value = false;
  }
};

// 删除模板
const deleteTemplate = async (template) => {
  try {
    await ElMessageBox.confirm('确定要删除这个模板吗？');
    await axios.delete(`/api/templates/${template.id}`);
    ElMessage.success('模板删除成功');
    fetchTemplates();
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除模板失败');
    }
  }
};

// ==================== 产品变体管理方法 ====================

// 添加新的产品选项
const addOption = () => {
  if (productOptions.value.length < 3) {
    productOptions.value.push({ name: '', values: [] });
    updateVariants();
  }
};

// 删除产品选项
const removeOption = (index) => {
  productOptions.value.splice(index, 1);
  updateVariants();
};

// 根据选项生成变体组合
const generateVariantCombinations = (optionsWithValues) => {
  if (optionsWithValues.length === 0) return [];
  
  const combinations = [[]];
  
  for (const option of optionsWithValues) {
    const newCombinations = [];
    for (const combination of combinations) {
      for (const value of option.values) {
        newCombinations.push([...combination, value]);
      }
    }
    combinations.splice(0, combinations.length, ...newCombinations);
  }
  
  return combinations;
};

// 更新变体列表
const updateVariants = () => {
  // 过滤出有名称和值的选项
  const validOptions = productOptions.value.filter(option => 
    option.name.trim() && option.values.length > 0
  );
  
  if (validOptions.length === 0) {
    generatedVariants.value = [];
    return;
  }
  
  // 生成所有可能的变体组合
  const combinations = generateVariantCombinations(validOptions);
  
  // 保留现有变体的数据，如果组合相同的话
  const existingVariantsMap = new Map();
  generatedVariants.value.forEach(variant => {
    const key = variant.options.join('|');
    existingVariantsMap.set(key, variant);
  });
  
  // 创建新的变体列表
  generatedVariants.value = combinations.map(combination => {
    const key = combination.join('|');
    const existing = existingVariantsMap.get(key);
    
    if (existing) {
      return existing;
    }
    
    // 创建新变体，使用基础价格作为默认值
    return {
      options: combination,
      option1: combination[0] || '',
      option2: combination[1] || '',
      option3: combination[2] || '',
      price: form.value.price || 0,
      compare_at_price: form.value.compare_at_price || 0,
      inventory_qty: 1000,
      sku: '',
      grams: 0,
      barcode: '',
      requires_shipping: true,
      taxable: true
    };
  });
  
  console.log('生成的变体:', generatedVariants.value);
};

// 统一设置变体价格
const resetVariantsPrices = async () => {
  const { value: price } = await ElMessageBox.prompt('请输入统一价格', '统一价格', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    inputPattern: /^\d+(\.\d{1,2})?$/,
    inputErrorMessage: '请输入有效的价格格式'
  });
  
  if (price) {
    generatedVariants.value.forEach(variant => {
      variant.price = parseFloat(price);
    });
    ElMessage.success('价格统一设置成功');
  }
};

// 自动生成SKU
const autoGenerateSkus = () => {
  const baseSku = form.value.name ? form.value.name.replace(/\s+/g, '-').toUpperCase() : 'PRODUCT';
  
  generatedVariants.value.forEach((variant, index) => {
    const optionCodes = variant.options.map(option => 
      option.substring(0, 2).toUpperCase()
    ).join('-');
    variant.sku = `${baseSku}-${optionCodes}-${String(index + 1).padStart(3, '0')}`;
  });
  
  ElMessage.success('SKU自动生成完成');
};

// 清空所有变体
const clearAllVariants = async () => {
  try {
    await ElMessageBox.confirm('确定要清空所有变体吗？这将重置产品选项配置。');
    productOptions.value = [{ name: '', values: [] }];
    generatedVariants.value = [];
    ElMessage.success('变体已清空');
  } catch (error) {
    // 用户取消操作
  }
};

// 在保存模板时处理变体数据
const prepareFormDataForSaving = () => {
  const formData = { ...form.value };
  
  // 将产品选项转换为Shopify格式
  const validOptions = productOptions.value.filter(option => 
    option.name.trim() && option.values.length > 0
  );
  
  formData.product_options = validOptions;
  formData.variants = generatedVariants.value;
  
  // 兼容旧版本的单选项格式
  if (validOptions.length > 0) {
    formData.option1_name = validOptions[0]?.name || '';
    formData.option1_value = validOptions[0]?.values[0] || '';
  }
  if (validOptions.length > 1) {
    formData.option2_name = validOptions[1]?.name || '';
    formData.option2_value = validOptions[1]?.values[0] || '';
  }
  if (validOptions.length > 2) {
    formData.option3_name = validOptions[2]?.name || '';
    formData.option3_value = validOptions[2]?.values[0] || '';
  }
  
  return formData;
};

const route = useRoute();

// 注册组件
defineOptions({
  components: {
    RichTextEditor
  }
});

onMounted(() => {
  console.log('Templates组件已挂载');
  fetchTemplates();
});

watch(() => route.path, (newPath, oldPath) => {
  console.log('路由变化:', { from: oldPath, to: newPath });
  if (newPath === '/templates') {
    console.log('进入模板页面，刷新列表');
    fetchTemplates();
  }
});
</script>

<style scoped>
.templates-container {
  padding: 40px;
  background: #f8fafc;
  min-height: 100vh;
  position: relative;
}

.templates-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 200px;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  z-index: 0;
}

.bg-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: -1;
}

.decoration-shape {
  position: absolute;
  background: linear-gradient(45deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-radius: 50%;
  filter: blur(50px);
  opacity: 0.5;
}

.shape-1 {
  width: 300px;
  height: 300px;
  top: -50px;
  left: -50px;
}

.shape-2 {
  width: 200px;
  height: 200px;
  bottom: 100px;
  right: 20%;
}

.shape-3 {
  width: 400px;
  height: 400px;
  bottom: 50px;
  left: 30%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  position: relative;
  z-index: 1;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.title-section {
  display: flex;
  align-items: center;
}

.title-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: #f3f4f6;
  border-radius: 12px;
  margin-right: 12px;
}

.title-icon {
  font-size: 28px;
  color: #4f46e5;
}

.title-text {
  display: flex;
  flex-direction: column;
}

.title-main {
  font-size: 24px;
  font-weight: 600;
  color: #111827;
}

.title-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.create-btn {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 500;
  border-radius: 6px;
  background: #4f46e5;
  border: none;
  transition: all 0.2s ease;
}

.create-btn:hover {
  background: #4338ca;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
  position: relative;
  z-index: 1;
}

.template-card {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
  position: relative;
  backdrop-filter: blur(10px);
  background-color: rgba(255, 255, 255, 0.8);
}

.template-card:hover {
  box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.15), 0 4px 8px -2px rgba(0, 0, 0, 0.08);
  transform: translateY(-5px);
}

.glass-card {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(229, 231, 235, 0.5);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.template-name {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.template-meta {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.separator {
  margin: 0 8px;
  color: #d1d5db;
}

.status-tag {
  font-weight: 500;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 13px;
}

.template-details {
  margin-bottom: 24px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f3f4f6;
}

.detail-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.detail-label {
  display: flex;
  align-items: center;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
  gap: 8px;
}

.detail-value {
  color: #6b7280;
  text-align: right;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.value.price {
  color: #059669;
  font-weight: 600;
}

.card-actions {
  display: flex;
  gap: 16px;
  padding-top: 16px;
  border-top: 1px solid #f3f4f6;
}

.action-btn {
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.edit-btn {
  background: #4f46e5;
  color: #fff;
}

.edit-btn:hover {
  background: #4338ca;
}

.delete-btn {
  background: #ef4444;
  color: #fff;
}

.delete-btn:hover {
  background: #dc2626;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 64px 24px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  backdrop-filter: blur(10px);
  background-color: rgba(255, 255, 255, 0.9);
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.empty-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  background: #f3f4f6;
  border-radius: 16px;
}

.empty-icon {
  font-size: 48px;
  color: #9ca3af;
}

.empty-title {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.empty-description {
  margin: 0 0 24px 0;
  font-size: 14px;
  color: #6b7280;
}

.create-first-btn {
  padding: 10px 20px;
  font-size: 14px;
  border-radius: 8px;
  background: #4f46e5;
  border: none;
  color: #fff;
  font-weight: 500;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.create-first-btn:hover {
  background: #4338ca;
}

:deep(.template-dialog) {
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.15), 0 4px 8px -2px rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(10px);
  background-color: rgba(255, 255, 255, 0.9);
}

:deep(.template-dialog .el-dialog__header) {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #fff;
}

:deep(.template-dialog .el-dialog__title) {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.template-form {
  padding: 24px 0;
}

.form-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f3f4f6;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.section-title {
  display: flex;
  align-items: center;
  margin: 0 0 20px 0;
  font-size: 18px;
  font-weight: 600;
  color: #374151;
}

.section-title .el-icon {
  margin-right: 8px;
  font-size: 20px;
  color: #3b82f6;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.form-item {
  margin-bottom: 0;
}

.form-item-full {
  grid-column: 1 / -1;
}

.form-tip {
  display: flex;
  align-items: center;
  margin-top: 8px;
  font-size: 13px;
  color: #6b7280;
}

.form-tip .el-icon {
  margin-right: 4px;
  font-size: 14px;
}

.dialog-footer {
  padding: 24px 0 0;
  border-top: 1px solid #f3f4f6;
  text-align: right;
}

.dialog-footer .el-button {
  margin-left: 12px;
  padding: 12px 24px;
  font-size: 16px;
  border-radius: 8px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
  color: #374151;
}

:deep(.el-input__wrapper) {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

:deep(.el-input__wrapper:hover) {
  border-color: #d1d5db;
}

:deep(.el-input__wrapper.is-focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

:deep(.el-select .el-input__wrapper) {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

:deep(.el-select .el-input__wrapper:hover) {
  border-color: #d1d5db;
}

:deep(.el-select .el-input__wrapper.is-focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

:deep(.el-textarea__inner) {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

:deep(.el-textarea__inner:hover) {
  border-color: #d1d5db;
}

:deep(.el-textarea__inner.is-focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

:deep(.el-input-number .el-input__wrapper) {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

:deep(.el-input-number .el-input__wrapper:hover) {
  border-color: #d1d5db;
}

:deep(.el-input-number .el-input__wrapper.is-focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.option-description {
  margin-bottom: 16px;
}

.option-description .el-alert {
  border-radius: 12px;
}

.option-description p {
  margin: 0;
  line-height: 1.5;
}

.field-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
  line-height: 1.4;
}

.option-example {
  margin-top: 20px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.option-example h5 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.option-example ul {
  margin: 0;
  padding-left: 20px;
  list-style-type: disc;
}

.option-example li {
  margin-bottom: 8px;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

.option-example li:last-child {
  margin-bottom: 0;
}

.option-example strong {
  color: #374151;
  font-weight: 600;
}

.advanced-section {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
  border: 1px solid rgba(102, 126, 234, 0.1);
  border-radius: 16px;
  position: relative;
}

.advanced-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px 16px 0 0;
}

.advanced-collapse {
  border: none !important;
  background: transparent !important;
}

:deep(.advanced-collapse .el-collapse-item__header) {
  border: none !important;
  background: transparent !important;
  padding: 16px 0 !important;
  font-weight: 500 !important;
  color: #4f46e5 !important;
}

:deep(.advanced-collapse .el-collapse-item__content) {
  padding: 0 0 16px 0 !important;
}

:deep(.advanced-collapse .el-collapse-item__wrap) {
  border: none !important;
}

.collapse-title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.collapse-title span {
  font-weight: 600;
  color: #4f46e5;
}

.collapse-title small {
  color: #6b7280;
  font-weight: 400;
  margin-left: 8px;
}

.simple-tip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 12px;
  margin-bottom: 20px;
  color: #1e40af;
  font-size: 14px;
  font-weight: 500;
}

:deep(.el-input--small .el-input__wrapper) {
  border-radius: 12px !important;
  border: 1px solid rgba(102, 126, 234, 0.2) !important;
  transition: all 0.3s ease !important;
}

:deep(.el-input--small .el-input__wrapper:hover) {
  border-color: rgba(102, 126, 234, 0.4) !important;
}

:deep(.el-input--small .el-input__wrapper.is-focus) {
  border-color: #667eea !important;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2) !important;
}

/* New styles for product options and variants */
.product-options-config {
  margin-bottom: 24px;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.config-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.config-header h5 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
}

.config-header p {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.option-group {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.05);
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.option-label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.option-name .el-form-item__label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.option-values .el-form-item__label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.option-values .el-select .el-input__wrapper {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.option-values .el-select .el-input__wrapper:hover {
  border-color: #d1d5db;
}

.option-values .el-select .el-input__wrapper.is-focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.values-hint {
  margin-top: 8px;
  font-size: 12px;
  color: #909399;
}

.option-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
}

.option-limit-hint {
  font-size: 13px;
  color: #6b7280;
}

.variants-management {
  margin-top: 24px;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.variants-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.variants-header h5 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
}

.variants-header p {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}

.variants-table-container {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.05);
}

.variants-table {
  width: 100%;
}

.variants-table th {
  background-color: #f3f4f6;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
  padding: 12px 16px;
  text-align: left;
}

.variants-table td {
  padding: 12px 16px;
  font-size: 14px;
  color: #4b5563;
  border-bottom: 1px solid #f3f4f6;
}

.variants-table td:last-child {
  border-bottom: none;
}

.variant-name {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.variant-tag {
  background-color: #e0f2fe;
  color: #1e40af;
  border-color: #90cdf4;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 6px;
}

.variants-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 16px;
}

.variants-actions .el-button {
  padding: 8px 16px;
  font-size: 14px;
  border-radius: 8px;
}

.simple-mode-hint {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f0f9eb;
  border: 1px solid #e1f3d8;
  border-radius: 12px;
  margin-top: 24px;
  color: #67c23a;
  font-size: 14px;
  font-weight: 500;
}

.hint-content {
  display: flex;
  flex-direction: column;
}

.hint-content h6 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
}

.hint-content p {
  margin: 0;
  line-height: 1.5;
  color: #6b7280;
}
</style> 