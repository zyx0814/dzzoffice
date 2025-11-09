<div align="center">
  <img src="./static/image/common/logo.png" width="128px" alt="DzzOffice" />
  <h1>DzzOffice</h1>
  <h4>多云存储 • 在线文档管理 • 协同办公 • 100%开源</h4>
  <p>
    <a href="http://dzzoffice.com" target="_blank">官网</a> •
    <a href="http://dzzoffice.com" target="_blank">下载</a> •
    <a href="http://demo.dzzoffice.com" target="_blank">演示</a>  • 
    <a href="./UPDATE.md" target="_blank">更新日志</a>
  </p>
  <p>
    <a href="https://hellogithub.com/repository/zyx0814/dzzoffice" target="_blank"><img src="https://abroad.hellogithub.com/v1/widgets/recommend.svg?rid=734f17351a6a4a0d9e94749651923a5e&claim_uid=wytcYV5NWvThIjQ&theme=neutral" alt="Featured｜HelloGitHub" style="width: 250px; height: 54px;" width="250" height="54" /></a>
  </p>
  <p>
    <a target="_blank" href="https://gitee.com/zyx0814/dzzoffice/stargazers"><img src="https://gitee.com/zyx0814/dzzoffice/badge/star.svg?theme=dark" alt='gitee star'/></a>
    <a target="_blank" href="https://github.com/zyx0814/dzzoffice/stargazers"><img alt="GitHub stars" src="https://img.shields.io/github/stars/zyx0814/dzzoffice?style=social"></a>
  </p>
</div>

## 📝 项目介绍

DzzOffice 是一款开源办公套件，旨在为企业和团队提供类似于“Google 企业应用套件”和“微软 Office365”的协同办公平台。它由多款开源办公应用组成，用户可根据需求选择和安装，实现高度灵活和可定制的使用体验。

作为集云存储与应用管理于一体的工具，DzzOffice 具备强大的文件共享功能和丰富的成员权限管理机制，广泛适用于个人云存储、团队网盘以及企业 OA 等多种场景。通过简单部署，它能接入多种云存储服务，轻松实现在线协同办公，有效提升团队工作效率。

#### 核心功能与工具组合
套件包含多款实用工具，用户可按需选择单一工具或组合使用，并能设置默认登录工具，兼顾强大功能与灵活适配性，满足不同规模企业和团队的需求。这些工具包含但不限于：
- **网盘**：企业、团队文件集中管理，支持按部门架构或灵活分组建立共享目录，提供文件标签、多版本管理、评论及精细化目录权限等协作功能。
- **文档/表格/演示文稿**：在线 Word、Excel、PPT 协作工具，前端支持企业自定义模板管理（如合同模板），后端兼容 office online server、onlyoffice、collaboraoffice 实现预览与协同编辑。
- **记录**：多人协作记录本，聚焦内容的协同记录与更新。
- **新闻**：企业级文章系统，可发布新闻、通知等重要信息。
- **通讯录**：整合企业人员联系方式，方便快速查询。
- **文集**：通过树形目录有序管理文档，支持 Markdown 编辑及 txt、epub、mobi、azw3 等格式的导入导出。
- **相册**：企业、团队图片集中管理与展示。
- **任务板**：可视化任务管理工具，助力团队协作推进工作。
- **讨论板**：内部沟通论坛，便于团队交流与问题探讨。
- **表单**：快速制作表单、问卷的工具，满足数据收集需求。

此外，DzzOffice 还集成了大量开源工具，如在线压缩/解压、多格式媒体文件预览、文档在线编辑等，充分展现了开源程序的多样化利用价值。同时，通过接入各类 web 应用，平台功能可实现无限扩展，能全面满足企业搭建高效便捷协同办公平台、个人获取完善云存储与协作工具的核心需求。

除开源版本外，DzzOffice 还提供商业版解决方案，以满足企业更专业化、定制化的办公需求，详情可参考[官方商业版页面](http://www.dzzoffice.com/business.html)。

更多应用可前往[DzzOffice 应用市场](http://www.dzzoffice.com/index.php?mod=dzzmarket)获取。

## 📥 安装部署

### 软件环境要求
1. **操作系统**：支持Linux、Windows、Mac OS，推荐使用Linux。
2. **数据库**：支持MySQL 5.5.3+（推荐MySQL 5.7+或MySQL 8.0+）、MariaDB 10.2+。
3. **Web服务器软件**：支持Apache 2.4+、Nginx 1.18+（推荐）、Zeus、IIS等，使用Nginx时需搭配php-fpm。
4. **PHP运行环境**：支持PHP 7.0+（推荐PHP 7.4或PHP 8.0+）。
5. **客户端浏览器**：推荐使用Chrome 60+、Firefox 60+、iOS 12+、IE 10+。
6. **离线部署说明**：DzzOffice完全支持离线部署，但部分插件（如“永中office预览”）的正常使用有赖于公网提供的服务，使用此类插件时需连接外网。

### 通用安装
1. 下载最新版安装包，将解压后的所有文件上传到服务器的 Web 根目录（如 /var/www/html 或面板创建的网站目录）；也可直接上传 zip 安装包后通过服务器工具在线解压。
2. 在浏览器上访问站点域名，程序会自动跳转到安装页面，按照提示安装即可。

### Docker 部署

镜像地址：https://github.com/zyx0814/dzzoffice-docker

## 🔝 升级方法

### 在线更新

1. 进入您原来的系统，关闭您的站点。进行数据备份；
2. 备份文件（如果有程序文件或风格文件的改动）；
3. 进入 管理 -> 系统工具 -> 在线更新，按提示完成更新任务；
4. 系统工具 -> 更新系统缓存；
5. 系统设置 -> 打开站点。

### 离线更新（仅支持从V2.01版本升级）

1. 进入您原来的系统，关闭您的站点。进行数据备份；
2. 备份文件（如果有程序文件或风格文件的改动）；
3. 下载并解压缩最新版的程序包；
4. 程序包解压缩后，并且将文件上传到网站根目录覆盖；
5. 访问 http://您的域名/install/update.php。
6. 按照程序提示，直至所有升级完毕。删除install/update.php 程序，以免被恶意利用。
7. 管理员登录后，系统工具 -> 更新系统缓存。
8. 系统设置 -> 打开站点。

## 📄 开源许可

* 本项目遵循 [AGPL-3.0](http://www.dzzoffice.com/licenses/license.txt) 开源许可协议

## 🤲 参与贡献

感谢您对 DzzOffice 的支持！每一个贡献都是项目进步的重要力量。我们欢迎通过以下方式参与共建：

### 如何参与？
#### 1. 反馈问题或建议
   - 通过 [GitHub](https://github.com/zyx0814/dzzoffice/) 或 [Gitee](https://gitee.com/zyx0814/dzzoffice/) 提交 Issue

#### 2. 代码贡献（PR流程）
   1. **Fork 仓库**  
      ➥ 访问 [GitHub 仓库](https://github.com/zyx0814/dzzoffice/) 点击 "Fork" 创建个人副本
   2. **本地修改**  
      ➥ 在您的 Fork 仓库中进行代码修改并提交
   3. **发起 PR**  
      ➥ 新建 Pull Request 向我们提交合并请求

#### 3. 关注动态
   - 关注 [GitHub 仓库](https://github.com/zyx0814/dzzoffice/) 了解最新动态。

让我们携手学习、共同进步，一起打造更加完善的 DzzOffice。

## 💬 加入社区

🤝 DzzOffice QQ交流群：与开发者和用户一起交流讨论，获取最新资讯和技术支持

**DzzOffice 交流群1：**[240726](https://qm.qq.com/q/gPvj9eNCAo "240726")

**DzzOffice 交流群2：**[245384](https://qm.qq.com/q/lwXQmUiI5G "245384")

**DzzOffice 交流群3：**[162934210](https://qm.qq.com/q/eHn2SHMiUS "162934210")

## 📝 友情提示
- 请随时关注更新动态，您可进行手动修补，让自己的站点时刻保持最安全的状态!

- V2.0 bata版本需先升级到V2.01版本，才能升级后续版本。

<div align="center">
  <h3>🌟 感谢您的关注</h3>
  <p>如果您觉得 DzzOffice 有价值，请给我们一个 ⭐ Star，这是对我们最大的鼓励！</p>
  <p>
    <strong>让我们一起构建更好的办公协作平台！ 🚀</strong>
  </p>
</div>
