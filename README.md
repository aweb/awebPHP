## Aweb PHP Smart Simple Framework

## 框架特性

- 为RESTful Api 接口设计
- composer 包管理
- psr-4 命名空间管理
- 简单、高效、稳定、实用、易拓展
- 集成FastRoute 路由
- 集成Medoo 数据库ORM管理
- 集成phpunit单元测试
- 集成swaggerAPI文档自动生成
- 集成monolog 日志管理
    - 错误日志记录
    - 详情请求日志记录
- App - API： 三层架构【Handler-入口层 -》Service-业务逻辑处理层 -》 Model-数据提供层】
- App - MVC:  四层架构【Handler-入口层 -》Service-业务逻辑处理层 -》 Model-数据提供层-》View -视图层】


## swagger API 接口生成说明
### 说明
- 禁止将 swagger目录部署到线上服务器
- swaggerApi访问地址： http:/doc.af.com/swagger/index.html
- swagger注解示列： https://github.com/zircote/swagger-php/tree/master/Examples/swagger-spec
### 部署方法
- api生成方法

```vendor/bin/swagger App/Handler --output Swagger/v1/swagger.json```

## Api接口 Demo演示
- 演示地址： http://api.af.com/v1/demo/list?username=hky
- aweb-framework.com.conf nginx配置(docker环境配置，适当修改)

```
server {
	listen       80;
	server_name  api.af.com;

	root   /var/www/aweb-framework/Public;
	index  index.php index.html index.htm;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_pass   php56:9000;
		fastcgi_index  index.php;
		fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
		include        fastcgi_params;
	}
}
```
- Api 目录结构

```
├── Bootstrap  // 项目初始化
├── App // 业务代码目录
│   ├── Handler  // 接口入口层（负责基本参数校验）
│   ├── Service // 业务处理代理层（负责主要业务逻辑聚合处理）
│   │   ├── BaseService  // 
│   ├── Model  // Model目录 数据提供与处理层
│   ├── View  // View目录 视图模版
│   └──
│ 
├── Config  // 配置文件目录
├── Core  // 框架核心目录
├── Logs  // 日志目录
│   ├── exception // 异常日志
│   ├── request // 接口请求日志
├── Test  // phpunit 单元测试目录
├── Utils  // 工具类
├── Public  // 项目入口目录，外网部署
│   ├── index.php // 单一入口
├── Swagger  // 生成api目录，内网环境部署
├── Vendor  // composer包管理
├── composer.json
├── README.md
└──  
```

## 开源项目与文档
- swagger-php https://github.com/zircote/swagger-php
- swagger-ui https://github.com/swagger-api/swagger-ui
- FastRoute https://github.com/nikic/FastRoute
- Medoo 

    https://github.com/catfan/Medoo
    
    https://medoo.lvtao.net/1.2/doc.php
    
- hassankhan/config 
  
  https://github.com/hassankhan/config

- Native PHP template system

   https://github.com/thephpleague/plates
   
   http://platesphp.com/v3/