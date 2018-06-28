
基于yii2的管理系统，运行环境与yii2(php>=5.4)一致。旨在为yii2爱好者提供一个基础功能稳定完善的系统，使开发者更专注于业务功能开发。
项目没有对yii2做任何的修改、封装，但是把yii2的一些优秀特性用在了项目上。

[![Latest Stable Version](https://poser.pugx.org/bluehe/app-basic/v/stable)](https://packagist.org/packages/bluehe/app-basic)
[![Latest Unstable Version](https://poser.pugx.org/bluehe/app-basic/v/unstable)](https://packagist.org/packages/bluehe/app-basic)
[![License](https://poser.pugx.org/bluehe/app-basic/license)](https://packagist.org/packages/bluehe/app-basic)
[![Build Status](https://www.travis-ci.org/bluehe/app-basic.svg?branch=master)](https://www.travis-ci.org/bluehe/app-basic)


更新记录
-------

0.1.0 版本基础功能开发


帮助
---------------

1. QQ 395868537

2. Email 395868537@qq.com



功能
---------------
 * 多登录注册
 * 短信接口
 * 站点状态控制
 
 
安装
---------------
前置条件: 如未特别说明，本文档已默认您把php命令加入了环境变量，如果您未把php加入环境变量，请把以下命令中的php替换成/path/to/php
1. 使用归档文件(简单，适合没有yii2经验者)
    >使用此方式安装，后台超管用户名和密码会在安装过程中让您填入
    1. 下载源码
    2. 解压到目录 
    3. 配置web服务器(参见下面)
    4. 完成
    
2. 使用composer (`推荐使用此方式安装`)
    >使用此方式安装，默认的后台超级管理员用户名admin密码1234
    
     >composer的安装以及国内镜像设置请点击 [此处](http://www.phpcomposer.com/)
     
     >以下命令默认您已全局安装composer，如果您是局部安装的composer:请使用php /path/to/composer.phar来替换以下命令中的composer
     
     1. 使用composer下创建项目
        
        ```bash
            $ composer create-project bluehe/app-basic webApp 
        ```
     2. 依次执行以下命令初始化yii2框架以及导入数据库
         ```bash
         $ cd webApp
         $ php ./init --env=Development #初始化yii2框架，线上环境请使用--env=Production
         $ php ./yii migrate/up --interactive=0 #导入 sql数据库，执行此步骤之前请先到common/config/main-local.php修改成正确的数据库配置
         ```
     3. 配置web服务器(参加下面)
     4. 完成
 
附:web服务器配置(注意是设置"path/to/app/web为根目录)
 
 * php内置web服务器(仅可用于开发环境,当您的环境中没有web服务器时)
 ```bash
  cd /path/to/app-basic
  php ./yii serve  
  
 ```
 
 * Apache
 ```bash
  DocumentRoot "path/to/app/web"
  <Directory "path/to/app/web">
      # 开启 mod_rewrite 用于美化 URL 功能的支持（译注：对应 pretty URL 选项）
      RewriteEngine on
      # 如果请求的是真实存在的文件或目录，直接访问
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      # 如果请求的不是真实文件或目录，分发请求至 index.php
      RewriteRule . index.php
  
      # ...其它设置...
  </Directory>
  ```
  
 * Nginx
 ```bash
 server {
     server_name  localhost;
     root   /path/to/app/web;
     index  index.php index.html index.htm;
     try_files $uri $uri/ /index.php?$args;
     
     location ~ /api/(?!index.php).*$ {
        rewrite /api/(.*) /api/index.php?r=$1 last;
     }
 
     location ~ \.php$ {
         fastcgi_pass   127.0.0.1:9000;
         fastcgi_index  index.php;
         fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
         include        fastcgi_params;
     }
 }
 ```
 
