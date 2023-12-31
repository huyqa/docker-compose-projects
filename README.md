# docker-compose-projects
##Trong file **docker-compose.yml** này:##
```
version: '3.1'

services:
  **nginx:**
    image: nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx:/etc/nginx/conf.d
      - ./nginx_logs:/var/log/nginx  # Thêm dòng này để liên kết volume

    networks:
      - wp-project_default
    command: /bin/bash -c "apt-get update && apt-get install -y net-tools nano && nginx -g 'daemon off;'"

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    restart: always
    ports:
      - "8081:80"
    environment:
      PMA_HOST: mysql
      PMA_USER: root
      PMA_PASSWORD: P@ssW0rd!@#
    networks:
      - wp-project_default

  wordpress:
    image: wordpress
    restart: always
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: mysql
      WORDPRESS_DB_USER: wordpressuser
      WORDPRESS_DB_PASSWORD: P@ssW0rd!@#
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./wordpress:/var/www/html
    networks:
      - wp-project_default

  mysql:
    image: mysql:5.7
    restart: always
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpressuser
      MYSQL_PASSWORD: P@ssW0rd!@#
      MYSQL_ROOT_PASSWORD: P@ssW0rd!@#
    volumes:
      - ./mysql:/var/lib/mysql
    networks:
      - wp-project_default

networks:
  wp-project_default:
    external: false
```
- Dịch vụ **nginx** cấu hình để lắng nghe cổng **80** và **443**, và sử dụng một volume để chia sẻ cấu hình Nginx từ thư mục **./nginx** trên máy host.
- Dịch vụ **phpmyadmin** sử dụng hình ảnh chính thức của PHPMyAdmin và kết nối đến dịch vụ MySQL.
- Dịch vụ **wordpress** sử dụng hình ảnh chính thức của WordPress và kết nối đến dịch vụ MySQL.
- Dịch vụ **mysql** sử dụng hình ảnh MySQL và cấu hình một số biến môi trường cho cơ sở dữ liệu.
Một mạng được định nghĩa với tên my_network được sử dụng để kết nối các dịch vụ với nhau.
Lưu ý rằng bạn cần thay thế các giá trị như **./nginx**, **./wordpress**, **./mysql** bằng đường dẫn thực tế trên máy host của bạn. Đồng thời, hãy cân nhắc thay đổi các biến môi trường và cấu hình để phản ánh yêu cầu cụ thể của bạn, chẳng hạn như tên miền, tên người dùng, mật khẩu, vv.
## tạo file nginx 
Dưới đây là một ví dụ cơ bản cho tệp cấu hình **default.conf.** Hãy thay đổi nó tùy thuộc vào yêu cầu cụ thể của bạn.
```server {
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
## Kiểm tra trạng thái các container:
```docker-compose ps```
## Truy cập WordPress:
Truy cập WordPress qua địa chỉ **http://localhost:8080** (hoặc thay localhost bằng địa chỉ IP hoặc tên miền của máy chủ nếu bạn đang triển khai trên một máy chủ từ xa).
## Truy cập PHPMyAdmin:
Truy cập PHPMyAdmin qua địa chỉ **http://localhost:8081** (hoặc tương tự, thay localhost bằng địa chỉ IP hoặc tên miền của máy chủ).
## Kiểm tra Nginx Reverse Proxy:
Kiểm tra xem Nginx có thể đúng cấu hình và chuyển hướng yêu cầu đến các dịch vụ khác nhau không. Thử truy cập **http://localhost/phpmyadmin** và **http://localhost:8080** để đảm bảo rằng Nginx đang hoạt động đúng.
## Quản lý và duy trì:
Để dừng tất cả các container: **docker-compose stop**
Để khởi động lại các container: **docker-compose start**
Để loại bỏ các container và mạng: **docker-compose down**
## Xóa Containers:
###### Xóa một container đã dừng:
```
docker rm <container_id>
```
Hoặc để xóa tất cả các containers đã dừng, bạn có thể sử dụng:
```
docker container prune
```
###### Xóa một container đang chạy:
Để xóa một container đang chạy, bạn cần dừng nó trước đó bằng lệnh:
```
docker stop <container_id>
```
Sau đó, xóa container như đã mô tả ở trên.
###### Xóa tất cả containers (bao gồm cả đang chạy và đã dừng):
```
docker rm $(docker ps -aq)
```
## Xóa Images:
###### Xóa một image:
```
docker rmi <image_id>
```
###### Xóa tất cả images chưa được sử dụng:
```
docker image prune
```
###### Xóa tất cả images (lưu ý: đây sẽ xóa tất cả images, bao gồm cả những images được sử dụng):
```
docker rmi $(docker images -q)
```
##Lưu ý quan trọng:
- Khi bạn xóa một container, dữ liệu bên trong container sẽ bị mất. Đảm bảo bạn đã lưu trữ dữ liệu cần thiết trước khi xóa.
- Khi bạn xóa một image, tất cả các container được tạo từ image đó sẽ không thể chạy nếu không có image.
## Tổng kết
- Bài viết trên tôi tổng hợp lại những kiến thức thu được khi sử dụng docker compose để setup môi trường PoC.
- Chắc chắn bài viết còn có nhiều thiếu sót, mong các bạn thông cảm và gửi feedback cho tôi để hoàn thiện thêm.
- Liên lạc của tôi:
```
- Email: huy.quach@huyqa-home.com
- Website: https://huyqa-home.com
```
Xin chân thành cảm ơn!
