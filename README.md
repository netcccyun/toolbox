
### 🎉 What's this？
这是一款`在线工具箱`程序，您可以通过安装扩展增强她的功能  
通过插件模板的功能，您也可以把她当做网页导航来使用~    

### 😺 演示地址

* <https://tool.cccyun.cc>

## 🎑 说明
> 严禁用于非法用途     

### 🎊 环境要求

* `PHP` >= 8.2
* `MySQL` >= 5.6
* `fileinfo`扩展
* 使用`Redis`缓存需安装`Redis`扩展

### 🚠 部署

* 从[Release页面](https://github.com/netcccyun/toolbox/releases)下载源代码

* 设置运行目录（绑定目录）为`public`

* 设置伪静态

* 如果是下载的Source code包，还需Composer安装依赖（Release页面下载的安装包不需要）
    + 配置阿里镜像源（国内服务器可选）
    ```
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    ```
    + 升级compose
    ```
    composer self-update
    ```
    + 安装依赖
    ```
    composer install --no-dev
    ```
    
* 打开网站会自动跳转到安装页面，根据界面提示完成安装。

* 更新方法：下载源码后直接上传覆盖即可

#### 🍰 伪静态

* Nginx
```
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
```
* Apache
```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```
### Docker部署方法

首先需要安装Docker，然后执行以下命令拉取镜像并启动（启动后监听8081端口）：

```
docker run --name toolbox -dit -p 8081:80 -v /var/toolbox:/app/www netcccyun/toolbox
```

从国内镜像地址拉取：

```
docker pull swr.cn-east-3.myhuaweicloud.com/netcccyun/toolbox:latest
```

#### 🍓 鸣谢

* [aoaostar](https://github.com/aoaostar/toolbox)
* vue
* thinkphp
* layui
* layuimini
* DashLite
