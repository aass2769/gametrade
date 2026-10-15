# GameTrade 애플리케이션 구조

상태: Laravel 구현 기준  
기준일: 2026-10-15  
요구사항: `docs/requirements.md`  
데이터: `docs/database-design.md`

요청은 `public/index.php`로 들어오고, `routes/web.php`와 `routes/api.php`가 컨트롤러로 보낸다. 화면은 Blade, 저장은 Eloquent, 데이터베이스는 Docker로 띄우는 MySQL 8.4다.

## 1. 디렉터리

```text
GameTrade/
├── app/
│   ├── Enums/ItemStatus.php          판매 상태와 허용된 다음 상태
│   ├── Exceptions/                   허용되지 않는 상태 변경
│   ├── Http/
│   │   ├── Controllers/              웹 화면 컨트롤러
│   │   ├── Controllers/Api/V1/       /api/v1 컨트롤러
│   │   ├── Requests/                 폼 검증
│   │   ├── Resources/ItemResource.php  API 응답 모양
│   │   └── ItemFilters.php           목록 검색 조건
│   ├── Models/                       User, Game, GameServer, Item
│   ├── Policies/ItemPolicy.php       작성자와 판매 중 여부
│   └── Services/Authenticator.php    로그인 확인
├── bootstrap/app.php                 라우트, 예외, 게스트 이동
├── config/                           Laravel 설정. 비밀 값은 .env
├── database/
│   ├── migrations/                   테이블 정의
│   └── seeders/SampleDataSeeder.php  샘플 회원과 상품
├── docker-compose.yml                이 프로젝트의 MySQL 컨테이너
├── public/
│   ├── index.php                     모든 HTTP 요청의 입구
│   └── css/style.css                 화면 스타일
├── resources/views/                  Blade 화면
├── routes/
│   ├── web.php                       화면 주소
│   └── api.php                       /api/v1 주소
├── storage/logs/                     오류 로그. 로그 파일은 Git에 넣지 않음
├── tests/                            PHPUnit
└── vendor/                           Composer 패키지. Git에 넣지 않음
```

`.env`에는 데이터베이스 비밀번호가 있다. Git에 넣지 않는다. 예시는 `.env.example`이다.

## 2. 요청이 지나가는 순서

1. 브라우저 요청이 `public/index.php`로 들어온다.
2. `bootstrap/app.php`가 웹 라우트 또는 API 라우트를 고른다.
3. 웹 요청은 세션과 CSRF를 검사한다. API 요청은 CSRF를 쓰지 않고, 로그인이 필요하면 Sanctum 토큰을 검사한다.
4. 입력이 있는 변경 요청은 Form Request가 값을 검사한다.
5. 컨트롤러가 Policy로 작성자를 확인한다.
6. 상품 수정, 삭제, 상태 변경은 `Item` 모델이 조건에 맞는 행만 바꾼다. 상태 변경은 트랜잭션 안에서 행을 잠근다.
7. 웹은 Blade로 HTML을 만들고, API는 JSON을 반환한다. 웹에서 변경이 성공하면 다른 주소로 리다이렉트한다.

## 3. 화면 주소

| 동작 | 메서드와 경로 |
| --- | --- |
| 목록 | `GET /` |
| 목록 검색 | `GET /?game_id=1&q=포션` |
| 상세 | `GET /items/{id}` |
| 등록 화면 | `GET /items/create` |
| 등록 | `POST /items` |
| 수정 화면 | `GET /items/{id}/edit` |
| 수정 | `PUT /items/{id}` |
| 삭제 확인 | `GET /items/{id}/delete` |
| 삭제 | `DELETE /items/{id}` |
| 상태 변경 | `POST /items/{id}/status` |
| 내 상품 | `GET /my/items` |
| 가입 화면 / 가입 | `GET /register`, `POST /register` |
| 로그인 화면 / 로그인 | `GET /login`, `POST /login` |
| 로그아웃 | `POST /logout`. `GET /logout`은 405 |

등록, 수정, 삭제, 상태 변경, 내 상품은 로그인이 필요하다. 가입과 로그인 화면은 이미 로그인한 사용자를 목록으로 보낸다.

## 4. API 주소

Laravel이 `routes/api.php` 앞에 `/api`를 붙인다. 실제 경로는 `/api/v1`이다.

| 동작 | 메서드와 경로 | 인증 |
| --- | --- | --- |
| 가입 | `POST /api/v1/register` | 없음. 응답에 토큰 |
| 로그인 | `POST /api/v1/login` | 없음. 응답에 토큰 |
| 로그아웃 | `POST /api/v1/logout` | Bearer 토큰 |
| 게임 목록 | `GET /api/v1/games` | 없음 |
| 상품 목록 / 상세 | `GET /api/v1/items`, `GET /api/v1/items/{id}` | 없음 |
| 등록 | `POST /api/v1/items` | Bearer 토큰 |
| 수정 | `PUT /api/v1/items/{id}` | Bearer 토큰, 작성자 |
| 삭제 | `DELETE /api/v1/items/{id}` | Bearer 토큰, 작성자 |
| 상태 변경 | `PATCH /api/v1/items/{id}/status` | Bearer 토큰, 작성자 |
| 내 상품 | `GET /api/v1/my/items` | Bearer 토큰 |

JSON에는 비밀번호를 넣지 않는다.

## 5. 클래스 역할

| 클래스 | 역할 |
| --- | --- |
| `ItemController`, `AuthController` | 웹 요청을 받고 화면 또는 리다이렉트를 반환 |
| `Api\V1` 컨트롤러 | 같은 규칙을 JSON으로 반환 |
| `RegisterRequest`, `ItemRequest`, `UpdateItemStatusRequest` | 입력 형식과 범위 검사 |
| `ItemPolicy` | 다른 회원이면 거절. 내용 수정과 삭제는 판매 중만 허용 |
| `Item` | 목록 조건, 판매 중일 때만 수정·삭제, 상태 전이 |
| `ItemStatus` | `ON_SALE`은 `IN_PROGRESS`만. `IN_PROGRESS`는 `COMPLETED` 또는 `ON_SALE`. `COMPLETED`는 다음이 없음 |
| `Authenticator` | 아이디가 없어도 같은 실패 문구를 내기 위해 더미 해시와 비교 |
| `ItemResource` | API에 내보낼 상품 필드 |

화면 파일은 `resources/views`에 있다. 공통 상단은 `layouts/app.blade.php`다.

## 6. 데이터베이스

`docker-compose.yml`의 MySQL 8.4가 `gametrade` 데이터베이스를 만든다. 테이블은 `php artisan migrate`가 `database/migrations`를 실행해서 만든다. 샘플 회원과 상품은 `php artisan db:seed`가 넣는다.

애플리케이션 데이터는 `users`, `games`, `game_servers`, `items`다. `sessions`, `cache`, `jobs`, `personal_access_tokens`, `migrations`는 현재 Laravel 설정이 데이터베이스에 세션, 캐시, 대기열, API 토큰, 마이그레이션 이력을 저장해서 생긴 테이블이다.

## 7. Spring Boot와 대응

| Spring Boot | 이 프로젝트 |
| --- | --- |
| `application.properties` | `.env`와 `config/` |
| DispatcherServlet | `public/index.php`와 `routes/` |
| Controller | `app/Http/Controllers` |
| `@Valid`와 Bean Validation | Form Request |
| Service의 상태 규칙 | `ItemStatus`와 `Item::transitionTo()` |
| Repository / JPA Entity | Eloquent 모델 |
| Thymeleaf | Blade |
| Spring Security 세션 | 웹 세션과 CSRF |
| 토큰 API | Sanctum Bearer 토큰 |
| Security의 인가 | `ItemPolicy` |
