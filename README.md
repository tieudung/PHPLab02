# PHPLab02 - Workshop Booking Portal

Ứng dụng thực hành môn Lập trình Web với PHP (Chủ đề: GET, POST, Server-side Validation, Sanitization, Cookies, Sessions, CSRF & PRG).

## 1. Cấu trúc thư mục
- `data/workshops.php`: Danh mục các khóa học workshop trên server.
- `lib/bootstrap.php`: Khởi tạo Session, CSRF token, đọc Cookie theme và nạp dữ liệu chung.
- `lib/functions.php`: Các hàm dùng chung (`e()`, `normalize_text()`, `validate_booking()`, `find_booking_index_by_ref()`, `find_workshop_for_booking()`, `csrf_valid()`, `redirect_to()`).
- `index.php`: Trang danh mục workshop, hỗ trợ tìm kiếm (`q`) và lọc theo chủ đề (`category`) bằng phương thức GET.
- `booking.php`: Trang đăng ký workshop mới (GET hiển thị form, POST kiểm tra dữ liệu và lưu vào Session).
- `dashboard.php`: Trang quản lý danh sách các booking trong phiên hiện tại (My Bookings).
- `booking_detail.php`: Xem chi tiết một booking theo mã `ref` qua phương thức GET.
- `booking_edit.php`: Chỉnh sửa thông tin booking (`full_name`, `email`, `quantity`) với kiểm tra CSRF, server-side validation và tính lại tổng tiền từ đơn giá trên server.
- `preference.php` & `logout.php`: Lưu giao diện sáng/tối bằng Cookie (30 ngày) và xóa phiên Session bằng POST.
- `request_demo.php`: Trang minh họa sự khác nhau giữa `$_GET`, `$_POST`, `$_REQUEST` và `REQUEST_METHOD`.

## 2. Cách chạy ứng dụng trên Localhost
### Cách 1: Chạy qua XAMPP Apache
1. Đặt thư mục `PHPLab02` vào trong thư mục `htdocs` của XAMPP.
2. Mở XAMPP Control Panel / Manager và bật **Apache Web Server**.
3. Truy cập trên trình duyệt tại địa chỉ: `http://localhost/PHPLab02/`

### Cách 2: Chạy bằng PHP Built-in Server
1. Mở Terminal tại thư mục `PHPLab02`.
2. Chạy lệnh:
   ```bash
   php -S localhost:8000