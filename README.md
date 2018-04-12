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

	root   /var/www/zestphp/Api/Public;
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
│   └──
│ 
├── Config  // 配置目录
├── Public  // 项目入口目录，外网部署
│   ├── index.php // 单一入口
├── Swagger  // 生成api目录，内网环境部署
├── Vendor  // composer包管理
├── composer.json
├── README.md
└──  
```