# Changelog

All notable changes to `laravel-cos` will be documented in this file.

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
