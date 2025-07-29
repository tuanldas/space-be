# Hướng dẫn đóng góp - Space BE

## Tổng quan

Tài liệu này định nghĩa các tiêu chuẩn, quy tắc và mô hình thiết kế được sử dụng trong dự án Space BE. Mọi thành viên và người đóng góp nên tuân theo các nguyên tắc này để đảm bảo tính nhất quán và chất lượng code.

## Quy trình đóng góp

1. **Fork dự án** và tạo branch mới từ `dev`
2. **Clone** repository về máy của bạn
3. **Commit** các thay đổi theo quy ước commit message
4. **Push** lên branch của bạn
5. **Tạo Pull Request** để merge vào branch `dev`

## Cấu trúc dự án

Dự án sử dụng framework Laravel với cấu trúc dự án được mở rộng theo mô hình layer:

- **Models**: Đại diện cho dữ liệu và quan hệ trong cơ sở dữ liệu
- **Repositories**: Xử lý truy vấn và thao tác dữ liệu
- **Services**: Chứa logic nghiệp vụ 
- **Controllers**: Xử lý request và trả về response
- **Requests**: Xác thực và định nghĩa các quy tắc validation cho request
- **Adapters**: Cung cấp giao diện giao tiếp với các dịch vụ bên ngoài
- **Enums**: Định nghĩa các giá trị enum

## Môi trường phát triển

1. **Yêu cầu hệ thống**:
   - Docker và Docker Compose
   - Git

2. **Cài đặt môi trường**:
   ```bash
   # Clone repository
   git clone <repository-url>
   cd space-be
   
   # Khởi động môi trường dev
   docker compose down
   docker compose up -d
   ```

## Mô hình thiết kế và nguyên tắc

1. **Repository Pattern**: Tách biệt logic truy vấn DB khỏi business logic
2. **Service Layer Pattern**: Đóng gói logic nghiệp vụ trong các service
3. **Interface-based Programming**: Sử dụng interface để định nghĩa hợp đồng và dependency injection
4. **Dependency Injection**: Tiêm các dependency thông qua constructor

## Quy tắc cụ thể

### Chung

1. **Ngôn ngữ**: 
   - Code sử dụng tiếng Anh
   - Comment sử dụng tiếng Việt
   - Commit message sử dụng tiếng Việt

2. **Định dạng**:
   - Indentation: 4 spaces
   - Sử dụng PSR-12

### Quy tắc đặt tên

1. **Classes**: 
   - PascalCase (VD: `TransactionCategory`)
   - Tên class nên mô tả chức năng

2. **Methods và Functions**: 
   - camelCase (VD: `getAllByUserAndType`)
   - Tên method nên bắt đầu bằng động từ

3. **Variables**:
   - camelCase
   - Tên biến nên có ý nghĩa và mô tả giá trị

4. **Constants**:
   - UPPER_SNAKE_CASE
   - Enum cases sử dụng UPPER_SNAKE_CASE

5. **Interfaces**:
   - PascalCase với hậu tố `Interface` (VD: `RepositoryInterface`)

### Models

1. **UUID**:
   - Sử dụng UUID v7 cho primary key
   - Định nghĩa method `newUniqueId()` trong model

2. **Properties**:
   - Định nghĩa rõ ràng `$fillable`, `$hidden`, `$casts`
   - Sử dụng type hinting trong PHPDoc cho collections

3. **Relationships**:
   - Định nghĩa rõ ràng và sử dụng return type

4. **Scopes**:
   - Sử dụng local scope cho các truy vấn thường xuyên sử dụng

### Repositories

1. **Tổ chức**:
   - Mỗi Repository phải kế thừa BaseRepository
   - Mỗi Repository phải implement interface tương ứng
   - Khai báo PHPDoc template với kiểu Model (VD: `@extends BaseRepository<TransactionCategory>`)

2. **Methods**:
   - Triển khai các phương thức CRUD cơ bản
   - Các truy vấn phức tạp nên được đặt trong repository

### Services

1. **Logic nghiệp vụ**:
   - Đặt tất cả logic nghiệp vụ trong service
   - Không đặt logic nghiệp vụ trong controllers

2. **Dependency Injection**:
   - Tiêm các dependency qua constructor
   - Sử dụng interfaces thay vì concrete classes

3. **Transaction**:
   - Xử lý transaction DB trong services

### Controllers

1. **Trách nhiệm**:
   - Chỉ xử lý request/response
   - Không chứa logic nghiệp vụ

2. **Response**:
   - Sử dụng các status code HTTP phù hợp
   - Format response JSON nhất quán

### API Responses

1. **Cấu trúc**:
   - Phản hồi thành công: Dữ liệu trực tiếp hoặc với message
   - Lỗi validation: Mảng errors và message
   - Các API lấy thông tin luôn được phân trang

2. **Status Codes**:
   - 200: Success
   - 201: Created
   - 204: No Content (DELETE)
   - 400: Bad Request
   - 401: Unauthorized
   - 403: Forbidden
   - 404: Not Found
   - 422: Unprocessable Entity (Validation errors)
   - 500: Server Error

### Validation

1. **Form Requests**:
   - Sử dụng Form Request classes cho validation
   - Định nghĩa rules trong method `rules()`

### Các quy tắc khác

1. **Internationalization**:
   - Sử dụng language files cho các text hiển thị
   - Sử dụng hàm `__()` cho các message

2. **File Upload**:
   - Sử dụng FileAdapter để xử lý việc lưu trữ file
   - Kiểm tra và giới hạn định dạng file

3. **Soft Delete**:
   - Sử dụng SoftDeletes trait cho các model cần tính năng này

4. **Comments và Documentation**:
   - PHPDoc cho methods và classes
   - Comment giải thích code phức tạp bằng tiếng Việt
   - Giữ lại PHPDoc comments, xóa các comment giải thích code khi không cần thiết

5. **Testing**:
   - Viết tests cho repositories, services và API endpoints
   - Sử dụng factories để tạo test data

6. **Docker**:
   - Chạy các lệnh thông qua Docker
   - Sử dụng Compose V2
   - Luôn chạy `docker compose down` trước khi `docker compose up -d`

## Quy trình phát triển

1. **Git**:
   - Commit message bằng tiếng Việt
   - Đảm bảo code đã được test trước khi commit

2. **Pull Request**:
   - Tạo PR với mô tả rõ ràng về các thay đổi
   - Code phải được review trước khi merge

3. **Deployment**:
   - Sử dụng các workflow CI/CD đã được thiết lập
   - Đảm bảo tests pass trước khi deploy 