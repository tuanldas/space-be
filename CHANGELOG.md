# Changelog

## [0.2.0]

### Thêm mới

    - Triển khai tính năng quản lý ví (wallets)
    - API danh sách ví với phân trang và lọc theo người dùng
    - API xem chi tiết ví
    - API tạo mới ví
    - API cập nhật thông tin ví
    - API xóa ví
    - API hiển thị tóm tắt ví cho sidebar
    - Triển khai tính năng quản lý giao dịch ví (wallet transactions)
    - API danh sách giao dịch theo ví
    - API xem chi tiết giao dịch
    - API tạo mới giao dịch (thu nhập/chi tiêu)
    - API xóa giao dịch
    - API lọc giao dịch theo loại (thu nhập/chi tiêu)
    - API lọc giao dịch theo khoảng thời gian
    - Cập nhật Postman collection với các API quản lý ví và giao dịch
    - Hệ thống test đầy đủ cho tính năng quản lý ví và giao dịch

### Cải tiến

    - Tái cấu trúc các file test để tổ chức theo tính năng và endpoint
    - Loại bỏ macro paginate trên Collection để tránh tác dụng phụ toàn cục

## [0.1.0]

### Thêm mới

    - Xây dựng hệ thống xác thực với OAuth và Passport
    - Postman collection với tính năng quản lý token tự động
    - Cài đặt thông tin đăng nhập admin mặc định
    - Cài đặt Laravel Horizon để quản lý queue
    - Thiết lập môi trường Docker cho development và production
    - Tính năng quản lý người dùng
    - API danh sách người dùng với phân trang và tìm kiếm
    - API xem chi tiết người dùng
    - API tạo mới người dùng
    - API cập nhật thông tin người dùng
    - API xóa người dùng
    - Test case cho tất cả các API quản lý người dùng
    - Cập nhật Postman collection với các API quản lý người dùng
    - Cài đặt theme cho ứng dụng
    - Gộp service php-fpm và vite trong môi trường local

### Thay đổi

- Cải thiện cấu hình Docker
