# Changelog

## [0.2.1] - 2025-08-12

### Cải tiến

* API ví: sắp xếp danh sách ví theo thời gian tạo mới nhất (created_at desc).
* Nghiệp vụ tạo ví:
  * Tự động tạo giao dịch thu nhập ban đầu nếu `balance > 0` (dùng enum `TransactionType`, mô tả i18n, lấy danh mục qua `TransactionCategoryService`, dùng hàm lấy danh mục mặc định không phân trang).
  * Chuẩn hóa DI (constructor property promotion) và bổ sung kiểu trả về cho `WalletService`/`WalletServiceInterface`.
* Nghiệp vụ cập nhật ví:
  * (1) Chỉ cho phép cập nhật `name` và `currency` (chuẩn hóa uppercase).
  * (2) Cấm đổi `currency` nếu ví đã có giao dịch.
* Bổ sung phương thức lấy danh mục mặc định đầu tiên theo loại trong repository/service (`getFirstDefaultByType`).

### Sửa lỗi

* Xử lý ảnh danh mục giao dịch:
  * Dùng `FileAdapterInterface::store`/`delete` thay cho phương thức không tồn tại, đảm bảo lưu/xóa đúng disk/path.
  * Khi cập nhật ảnh: cập nhật nếu đã có, tạo mới nếu chưa có.
  * Khi xóa vĩnh viễn: xóa ảnh cả khi danh mục đang ở thùng rác.
* Cập nhật test (WalletService, TransactionCategory) và đảm bảo toàn bộ test xanh.

### Khác

* Dọn dẹp comment giải thích, giữ nguyên PHPDoc; tuân thủ quy ước commit tiếng Việt.

## [0.2.0]

### Thêm mới

* Triển khai tính năng quản lý ví (wallets)
* API danh sách ví với phân trang và lọc theo người dùng
* API xem chi tiết ví
* API tạo mới ví
* API cập nhật thông tin ví
* API xóa ví
* API hiển thị tóm tắt ví cho sidebar
* Triển khai tính năng quản lý giao dịch ví (wallet transactions)
* API danh sách giao dịch theo ví
* API xem chi tiết giao dịch
* API tạo mới giao dịch (thu nhập/chi tiêu)
* API xóa giao dịch
* API lọc giao dịch theo loại (thu nhập/chi tiêu)
* API lọc giao dịch theo khoảng thời gian
* Cập nhật Postman collection với các API quản lý ví và giao dịch
* Hệ thống test đầy đủ cho tính năng quản lý ví và giao dịch

### Cải tiến

* Tái cấu trúc các file test để tổ chức theo tính năng và endpoint
* Loại bỏ macro paginate trên Collection để tránh tác dụng phụ toàn cục

## [0.1.0]

### Thêm mới

* Xây dựng hệ thống xác thực với OAuth và Passport
* Postman collection với tính năng quản lý token tự động
* Cài đặt thông tin đăng nhập admin mặc định
* Cài đặt Laravel Horizon để quản lý queue
* Thiết lập môi trường Docker cho development và production
* Tính năng quản lý người dùng
* API danh sách người dùng với phân trang và tìm kiếm
* API xem chi tiết người dùng
* API tạo mới người dùng
* API cập nhật thông tin người dùng
* API xóa người dùng
* Test case cho tất cả các API quản lý người dùng
* Cập nhật Postman collection với các API quản lý người dùng
* Cài đặt theme cho ứng dụng
* Gộp service php-fpm và vite trong môi trường local

### Thay đổi

* Cải thiện cấu hình Docker
