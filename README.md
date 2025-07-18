# Shopify商品批量上传系统

这是一个基于Laravel和Vue.js开发的Shopify商品批量上传系统。系统支持商品模板管理、批量导入导出、变体管理等功能。

## 系统要求

- PHP >= 8.2
- Composer 2.x
- Node.js >= 18.x
- MySQL >= 8.0
- Nginx/Apache

## 安装步骤

### 1. 克隆项目

```bash
git clone https://github.com/jakonwang/shopify-import.git
cd shopify-import
```

### 2. 安装PHP依赖

```bash
composer install
```

### 3. 安装前端依赖

```bash
npm install
```

### 4. 环境配置

```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 编辑.env文件，配置数据库信息
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. 数据库迁移

```bash
php artisan migrate
```

### 6. 编译前端资源

```bash
# 开发环境
npm run dev

# 生产环境
npm run build
```

### 7. 启动开发服务器

```bash
# 使用内置命令启动所有服务（推荐）
composer run dev

# 或分别启动服务
php artisan serve  # Laravel服务
php artisan queue:work  # 队列处理
npm run dev  # Vite开发服务器
```

## 部署说明

### Nginx配置示例

```nginx
server {
    listen 80;
    server_name your_domain.com;
    root /path/to/your/project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache配置

Apache服务器需要启用mod_rewrite模块，项目根目录下的.htaccess文件已包含必要的重写规则。

### 生产环境优化

```bash
# 优化类加载
composer install --optimize-autoloader --no-dev

# 缓存配置
php artisan config:cache

# 缓存路由
php artisan route:cache

# 缓存视图
php artisan view:cache
```

## 更新说明

当需要更新系统时，请按以下步骤操作：

```bash
# 1. 拉取最新代码
git pull

# 2. 安装/更新依赖
composer install
npm install

# 3. 清理缓存
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 4. 执行数据库迁移
php artisan migrate

# 5. 重新编译前端资源
npm run build

# 6. 重启队列处理器
php artisan queue:restart
```

## 常见问题

1. 如果遇到权限问题，请确保storage和bootstrap/cache目录可写：
```bash
chmod -R 775 storage bootstrap/cache
```

2. 如果前端资源无法加载，请检查：
   - .env文件中的APP_URL配置是否正确
   - 是否已运行npm run build
   - 是否已正确配置服务器重写规则

3. 如果队列不工作，请检查：
   - 是否已配置队列驱动（推荐使用redis或database）
   - 是否已启动队列处理器
   - 是否已配置Supervisor管理队列进程（生产环境推荐）

## 技术支持

如有问题，请提交Issue或联系技术支持。
