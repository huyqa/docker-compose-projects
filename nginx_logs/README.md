# docker-compose-projects
## Đây là file test mẫu nginx
###### Thêm file **nano default.conf**
```
server {
    listen 80;
    server_name your_domain.com www.your_domain.com;

    location / {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /phpmyadmin {
        proxy_pass http://phpmyadmin:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Add other locations or configurations as needed

    location ~ /\. {
        deny all;
    }
}
```
- Đảm bảo rằng bạn đã thay thế **our_domain.com** bằng tên miền thực tế của bạn và đã điều chỉnh cấu hình Nginx để phản ánh yêu cầu của bạn.
## Lưu và đóng trình soạn thảo:
- Trong nano, bạn có thể nhấn **Ctrl + X** để thoát.
- Sau đó, nhập **Y** để xác nhận bạn muốn lưu thay đổi.
- Nhấn **Enter** để xác nhận.
- Bây giờ bạn đã tạo một tệp cấu hình Nginx mới. 
- Khi bạn triển khai lại Docker Compose (sử dụng **docker-compose up -d --force-recreate**), Nginx sẽ sử dụng cấu hình mới này để chuyển hướng yêu cầu đến các dịch vụ khác nhau như WordPress và PHPMyAdmin.
## Tổng kết
- Bài viết trên tôi tổng hợp lại những kiến thức thu được khi sử dụng docker compose để setup môi trường PoC.
- Chắc chắn bài viết còn có nhiều thiếu sót, mong các bạn thông cảm và gửi feedback cho tôi để hoàn thiện thêm.
- Liên lạc của tôi:
```
- Email: huy.quach@huyqa-home.com
- Website: https://huyqa-home.com
```
Xin chân thành cảm ơn!
