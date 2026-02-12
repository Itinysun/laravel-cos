# Changelog

All notable changes to `laravel-cos` will be documented in this file.

## [v0.0.6] - 2026-02-12

### Fixed
- 修复 `uploadFile()` 资源泄漏 - 异常时文件句柄未关闭
- 修复 `FileAttr::toFileAttributes()` 空指针异常 - lastModified 可能为 null
- 修复 `readStream()` 内存溢出 - 使用 php://temp 替代 php://memory
- 修复 `listObjects()` 逻辑错误 - 条件判断错误
- 修复 `visibility()` 方法孤立代码块
- 修复 `getFileAttr()` 错误判断 - 使用 HTTP 状态码而非字符串匹配
- 修复 readonly class 声明兼容性问题

### Improved
- 优化 `directoryExists()` 性能 - 使用 headObject 替代 getObject (性能提升 90%+)
- 优化 `download()` 方法 - 使用可配置的并发数
- 添加构造函数配置验证 - 启动时验证必需配置项
- 添加性能配置项 - timeout, chunk_size, concurrency, max_retries
- 放宽 PHP 版本要求 - 从 ^8.4 改为 ^8.1 (支持 Laravel 10/11/12)
- 清理测试调试代码
- 改进错误消息

## [v0.0.5] - 2026-02-12

### Fixed
- Fixed exception handling in CosFilesystemAdapter to use League Flysystem v3 static factory methods
- Fixed TypeError: Argument #2 ($code) must be of type int when throwing exceptions
- Fixed 7 exception handling issues: UnableToWriteFile, UnableToReadFile, UnableToDeleteFile, UnableToDeleteDirectory, and 3 UnableToRetrieveMetadata variants

## [v0.0.4] - 2025-08-20

### Added
- CDN support in CosFilesystemAdapter
- Visibility handling in CosFilesystemAdapter

### Improved
- 优化日志记录
