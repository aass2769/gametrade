# GameTrade 애플리케이션 구조

상태: 설계안 (구현 전)  
기준일: 2026-10-14  
요구사항: `docs/requirements.md`  
데이터: `docs/database-design.md`

순수 PHP에 맞게, 공개 진입 파일과 재사용 함수를 나눈다. 프레임워크의 컨트롤러, 서비스, 리포지토리 클래스를 그대로 만들지 않는다.

## 1. 디렉터리

Apache의 DocumentRoot는 `public/`만 가리킨다. `config/`, `src/`, `sql/`, `docs/`, `storage/`는 URL로 열 수 없게 둔다.

```text
GameTrade/
├── config/
│   ├── config.example.php     저장소에 포함하는 설정 예시. 비밀번호 자리만 비움
│   └── config.php             로컬 접속 정보. Git에 넣지 않음
├── docs/                      설계 문서
├── public/                    DocumentRoot
│   ├── index.php              목록, 검색, 게임 필터
│   ├── item.php               상세, 작성자용 상태 변경 폼 출력
│   ├── item-create.php        등록 폼 GET, 등록 POST
│   ├── item-edit.php          수정 폼 GET, 수정 POST
│   ├── item-delete.php        삭제 확인 GET, 삭제 POST
│   ├── item-status.php        상태 변경 POST
│   ├── my-items.php           내 판매 상품 GET. P0. 정렬과 페이지네이션 없음
│   ├── register.php           회원가입 GET, POST
│   ├── login.php              로그인 GET, POST
│   ├── logout.php             로그아웃 POST
│   └── assets/
│       └── css/
│           └── style.css      공통 스타일
├── router.php                 PHP 내장 서버용. 없는 경로는 404
├── sql/                       구현 단계에서 schema.sql, seed.sql을 둘 위치
├── src/
│   ├── bootstrap.php          설정 로드, 세션, 공통 파일 포함
│   ├── db.php                 PDO 연결
│   ├── auth.php               로그인 상태, 로그인 필수, 세션 갱신
│   ├── csrf.php               토큰 생성과 검사
│   ├── helpers.php            출력 이스케이프, 리다이렉트, 플래시 메시지
│   ├── users.php              회원 조회와 가입
│   ├── items.php              상품 조회와 변경, 상태 전환 규칙
│   └── layout/
│       ├── header.php         공통 상단, 내비게이션, 플래시 출력
│       ├── footer.php         공통 하단
│       └── item-form.php      등록·수정 폼 필드
├── storage/
│   └── logs/                  오류 로그. Git에 로그 파일은 넣지 않음
└── README.md                  구현과 검증이 끝난 뒤 작성
```

JavaScript 파일은 P0에 두지 않는다. 나중에 보조 기능이 생겨도 `public/assets/js/`에만 둔다. 핵심 POST 동작은 그 파일 없이 완료되어야 한다.

`sql/`과 `storage/logs/`는 설계 단계에서 비워 둔다. 구현 2단계에서 디렉터리와 `config.example.php`를 만든다.

## 2. 공개 여부와 설정

| 경로 | URL 접근 | 이유 |
| --- | --- | --- |
| `public/` | 허용 | HTML을 만들고 폼을 받는 진입 파일과 CSS |
| `config/config.php` | 금지 | DB 계정 |
| `src/` | 금지 | 쿼리와 인증 로직. 직접 실행할 진입점이 아님 |
| `storage/logs/` | 금지 | 오류 내용에 SQL과 경로가 남을 수 있음 |
| `sql/`, `docs/` | 금지 | 스키마와 설계 메모 |

XAMPP에서는 프로젝트 폴더 전체를 `htdocs`에 두고 DocumentRoot를 프로젝트 루트로 쓰지 않는다. `public`을 가리키는 가상 호스트나, `htdocs/gametrade` → `GameTrade/public` 링크를 사용한다.

가상 호스트 예:

```apache
DocumentRoot "/Users/woomin/Desktop/project/php/GameTrade/public"
<Directory "/Users/woomin/Desktop/project/php/GameTrade/public">
    AllowOverride None
    Require all granted
    DirectoryIndex index.php
</Directory>
```

구현 때 Apache 설정 파일 경로를 확인하고, 저장소에는 예시만 README에 적는다. 전역 `httpd.conf`를 프로젝트 안에 복사하지 않는다.

보조 장치로 프로젝트 루트에 다음 `.htaccess`를 둔다. DocumentRoot를 잘못 지정했을 때의 대비다. 주 보호 수단은 DocumentRoot다.

```apache
Require all denied
```

`public/`은 가상 호스트가 직접 열므로 이 파일의 영향을 받지 않아야 한다. 루트 `.htaccess`가 `public`까지 막으면 DocumentRoot 설정이 잘못된 것이다. 구현 후 브라우저로 `http://localhost/config/config.php`가 열리지 않는지 확인한다.

`config.php`가 실수로 실행되어도 배열을 return만 하고 echo하지 않는다. 이것만으로 충분하다고 보지 않는다. URL로 PHP 소스가 텍스트로 내려가면 비밀번호가 보인다.

Git에 넣지 않을 경로:

- `config/config.php`
- `storage/logs/*.log`

`config.example.php`에는 키 이름만 둔다.

```php
<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'gametrade',
        'user' => 'gametrade_user',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
];
```

위 배열은 설정 형태를 정하는 설계다. 구현 전에 파일을 만들지 않는다.

## 3. 요청이 지나가는 순서

브라우저가 `public` 아래 PHP 한 파일을 호출하면 그 요청이 끝나고 프로세스는 요청 사이에 변수를 유지하지 않는다. 로그인 유지는 세션 쿠키로만 이어진다.

공통 순서:

1. 진입 파일이 `src/bootstrap.php`를 한 번 포함한다.
2. `bootstrap.php`가 설정을 읽고, 세션 쿠키 옵션을 정한 뒤 `session_start()`를 호출한다.
3. PDO, 인증, CSRF, 화면 보조 함수를 로드한다.
4. 진입 파일이 메서드와 로그인 여부를 검사한다.
5. POST면 CSRF를 검사하고, `users.php` 또는 `items.php`의 함수로 DB 작업을 맡긴다.
6. 성공한 변경은 리다이렉트한다. 검증 실패는 같은 진입 파일이 폼 HTML을 다시 그린다.
7. HTML 앞뒤에서 `layout/header.php`, `layout/footer.php`를 포함한다.

진입 파일은 SQL 문자열을 직접 가지지 않는다. 쿼리는 `users.php`와 `items.php`에 둔다. 진입 파일은 요청 값을 읽고, 함수를 호출하고, 결과를 출력한다.

## 4. 파일별 역할

### 4.1 `src/bootstrap.php`

- `config/config.php`가 없으면 사용자에게는 "설정을 확인할 수 없습니다."만 보여 주고, 로그에 기대한 경로를 남긴다.
- `display_errors`는 끄고, `log_errors`는 `storage/logs/app.log`로 켠다.
- 세션 쿠키는 `HttpOnly`, `SameSite=Lax`로 둔다. 로컬 HTTP에서는 `Secure`를 끄고, 그 사실을 개발 환경 절에 적는다. HTTPS로 올릴 때만 `Secure`를 켠다.
- `session.use_strict_mode`를 켠다.
- 시간대는 `Asia/Seoul`로 맞춘다.

### 4.2 `src/db.php`

- DSN에 `host`, `port`, `dbname`, `charset=utf8mb4`를 넣는다.
- 옵션은 예외 모드, 연관 배열 fetch, `EMULATE_PREPARES = false`다.
- 연결 실패 예외는 로그로 남기고 사용자에게는 "잠시 후 다시 시도해 주세요."를 보여 준다.
- 함수 `db(): PDO`가 같은 요청 안에서 연결을 한 번만 만든다.

`EMULATE_PREPARES = false`는 LIMIT/OFFSET를 정수 파라미터로 바인딩할 때 필요하다. P0에서 LIMIT를 쓰지 않더라도 연결 옵션은 처음부터 이렇게 둔다.

### 4.3 `src/auth.php`

| 함수 | 역할 |
| --- | --- |
| `currentUser(): ?array` | 세션의 `user_id`, `username`. 없으면 null |
| `requireLogin(): void` | 비로그인이면 로그인 페이지로 이동하고 종료 |
| `loginUser(array $user): void` | `session_regenerate_id(true)` 후 세션에 id와 username 저장 |
| `logoutUser(): void` | 세션 비우기, 쿠키 삭제, `session_destroy()` |

세션에는 비밀번호 해시를 넣지 않는다.

### 4.4 `src/csrf.php`

| 함수 | 역할 |
| --- | --- |
| `csrfToken(): string` | 세션에 32바이트 난수 토큰이 없으면 만들어 반환 |
| `csrfField(): string` | hidden input HTML. 값은 이스케이프 |
| `verifyCsrf(): void` | POST의 토큰과 세션 토큰을 `hash_equals`로 비교. 실패하면 403 후 종료 |

토큰은 로그인 성공 시 세션 재발급과 함께 새로 만든다.

### 4.5 `src/helpers.php`

| 함수 | 역할 |
| --- | --- |
| `e(?string $value): string` | `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. null은 빈 문자열 |
| `redirect(string $path): void` | 같은 사이트의 상대 경로만 허용하고 `Location` 후 종료 |
| `setFlash(string $message): void` | 다음 요청에 한 번 보여 줄 메시지 |
| `getFlash(): ?string` | 메시지를 읽고 세션에서 제거 |
| `statusLabel(string $status): string` | DB 코드를 한글 라벨로 바꿈. 모르는 코드는 "알 수 없음" |

`redirect()`에 외부 URL을 허용하지 않는다. 오픈 리다이렉트용 파라미터는 만들지 않는다.

### 4.6 `src/users.php`

| 함수 | 역할 |
| --- | --- |
| `findUserByUsername(string $username): ?array` | 로그인, 중복 확인 |
| `createUser(string $username, string $passwordHash): void` | INSERT. 중복 키는 호출자가 메시지로 바꿈 |

비밀번호 검증 규칙 함수는 `users.php`에 둔다. 해시 생성은 이 파일이 `password_hash`를 호출한다. 진입 파일은 해시를 직접 만들지 않아도 된다. 진입 파일은 평문을 함수에 넘기고, 함수가 해시한다.

로그인 비교는 `password_verify`만 사용한다. `==`로 해시를 비교하지 않는다.

### 4.7 `src/items.php`

| 함수 | 역할 |
| --- | --- |
| `listGames(): array` | 필터용 게임 목록. `id` 순 |
| `listServers(): array` | 등록 폼의 선택 목록. 게임명 포함 |
| `searchItems(array $filters): array` | 목록. 필터 키는 `game_id`, `q`, P1의 `sort`, `page`만 인정 |
| `findItem(int $id): ?array` | 상세 |
| `listItemsByUser(int $userId): array` | 내 판매 상품 |
| `validateItemInput(array $input): array` | 오류 메시지 배열과 정제된 값 |
| `createItem(int $userId, array $data): int` | INSERT 후 새 id |
| `updateItem(int $id, int $userId, array $data): bool` | 본인, `ON_SALE`만 |
| `deleteItem(int $id, int $userId): bool` | 본인, `ON_SALE`만 |
| `allowedNextStatuses(string $current): array` | 전이 표 |
| `changeItemStatus(int $id, int $userId, string $next): bool` | 허용된 전이만 UPDATE |

전이 표는 이 파일의 배열 한 곳에만 둔다.

```text
ON_SALE     => [IN_PROGRESS]
IN_PROGRESS => [COMPLETED, ON_SALE]
COMPLETED   => []
```

### 4.8 레이아웃

`header.php`는 HTML 선언, `charset=UTF-8`, 제목, CSS 링크, 상단 메뉴, 플래시 메시지를 출력한다.

메뉴:

- 항상: GameTrade(목록으로), 현재는 텍스트 로고
- 비로그인: 로그인, 회원가입
- 로그인: 아이템 등록, 내 판매 상품, 아이디, 로그아웃 POST 폼

`footer.php`는 하단과 `body`를 닫는다. 공통 레이아웃에 상품 쿼리를 넣지 않는다.

P0 CSS는 읽기 쉬운 단일 열과 표다. 색과 배치는 새로 정하고, 특정 거래 사이트의 레이아웃을 따라 하지 않는다. P1에서 너비 약 720px 이하일 때 표 열을 블록으로 쌓는다.

### 4.9 진입 파일

| 파일 | 메서드 | 인증 | 하는 일 |
| --- | --- | --- | --- |
| `index.php` | GET | 공개 | 목록, 검색, 게임 필터. P1에서 정렬과 페이지 |
| `item.php` | GET | 공개 | 상세. 작성자면 수정·삭제 링크와 상태 폼 |
| `item-create.php` | GET, POST | 회원 | 등록 |
| `item-edit.php` | GET, POST | 회원, 작성자, `ON_SALE` | 수정 |
| `item-delete.php` | GET, POST | 회원, 작성자, `ON_SALE` | GET은 확인, POST는 삭제 |
| `item-status.php` | POST | 회원, 작성자 | 상태 변경. GET은 405 |
| `my-items.php` | GET | 회원 | 내 상품. P0 |
| `register.php` | GET, POST | 비로그인만 의미 있음 | 가입. 로그인 상태면 목록으로 |
| `login.php` | GET, POST | 공개 | 로그인. 로그인 상태면 목록으로 |
| `logout.php` | POST | 회원 | 로그아웃. GET은 405 |

상태 변경만 별도 파일로 나눈다. 상세 페이지가 POST까지 처리하면 조회와 변경이 한 파일에 섞이고, 새로고침 재전송을 나누기 어렵다. 폼의 `action`은 `item-status.php`다.

삭제 확인을 별도 GET으로 둔 이유는 링크를 여는 동작과 삭제 실행을 분리하기 위해서다.

## 5. 화면 구성

### 5.1 목록

- 게임 `<select name="game_id">`와 검색 `<input name="q">`를 GET 폼으로 둔다.
- P1에서 정렬 `<select name="sort">`와 이전/다음 링크를 추가한다.
- 표 열: 게임, 서버, 아이템명, 가격, 수량, 상태, 등록일
- 아이템명은 `item.php?id=`로 연결한다.

### 5.2 상세

- 정의 목록(`dl`)으로 게임, 서버, 아이템명, 가격, 수량, 상태, 판매자, 설명, 등록일, 수정일을 보여 준다.
- 작성자이고 `ON_SALE`이면 수정 링크와 삭제 확인 링크를 보여 준다.
- 작성자이고 다음 상태가 있으면 상태 선택과 변경 버튼을 POST 폼으로 보여 준다.

### 5.3 등록과 수정 폼

필드 이름:

| name | 설명 |
| --- | --- |
| `server_id` | 필수 select. 옵션 문구는 `게임명 - 서버명`. `optgroup`으로 게임을 묶는다 |
| `item_name` | 텍스트 |
| `price` | 텍스트. 숫자 키보드를 유도해도 서버가 다시 검사 |
| `quantity` | 텍스트 |
| `description` | textarea |
| `csrf_token` | hidden |

수정 폼은 `id`를 hidden으로 둔다. `user_id` hidden은 두지 않는다.

게임 id를 폼에서 따로 받지 않는다. 서버를 검사할 때 게임명을 함께 읽어 화면에 쓴다. 목록 필터만 `game_id`를 받는다.

### 5.4 그 외

- 회원가입: `username`, `password`, `password_confirm`, `csrf_token`
- 로그인: `username`, `password`, `csrf_token`
- 삭제 확인: 상품명과 "삭제합니다" 버튼. POST `id`, `csrf_token`
- 로그아웃: 버튼만. POST `csrf_token`

검증 오류는 해당 필드 위에 문장으로 보여 준다. 한 요청에 여러 오류가 있으면 모두 보여 준다.

## 6. Spring Boot 구조와 대응

개념적으로 비슷한 부분:

| Spring Boot에서 익숙한 것 | 이 프로젝트 |
| --- | --- |
| `application.properties`의 datasource | `config/config.php` |
| DispatcherServlet으로 들어오는 요청 | Apache가 실행하는 `public/*.php` |
| Controller | 각 진입 파일. URL을 어노테이션으로 매핑하지 않고 파일 이름이 경로다 |
| Repository의 SQL | `src/users.php`, `src/items.php`의 함수 |
| Service의 상태 규칙 | `allowedNextStatuses()`와 검증 함수. 별도 서비스 클래스는 두지 않음 |
| View template | 진입 파일의 HTML과 `layout/` |
| 로그인 세션 | PHP 세션. Spring Security 필터 체인은 없음 |
| Bean 주입 | 없음. 함수와 `require_once` |

다른 부분:

- 요청마다 PHP가 파일을 처음부터 실행한다. 상주하는 애플리케이션 컨텍스트가 없다.
- 라우터, DI, ORM, 템플릿 엔진을 넣지 않는다. SQL을 직접 쓰는 것이 학습 목표다.
- 진입 파일이 요청 처리와 HTML 출력을 같이 가진다. SQL만 분리한다. Controller와 Thymeleaf를 억지로 파일 단위로 복제하지 않는다.
- 계층을 `controller/service/repository/dto` 디렉터리로 나누지 않는다. 파일이 늘지 않는 규모이고, 그 구조는 프레임워크 없이 보일러플레이트만 늘어난다.

진입 파일이 길어지면 그 파일 안에서 HTML 블록을 함수로 나누기보다, 쿼리와 검증을 `src`에 남겼는지 먼저 확인한다.

## 7. URL

로컬 기본 경로는 DocumentRoot가 `public`일 때의 경로다.

| 동작 | 메서드와 경로 |
| --- | --- |
| 목록 | `GET /index.php` |
| 목록 검색 | `GET /index.php?game_id=1&q=포션` |
| 상세 | `GET /item.php?id=10` |
| 등록 화면 | `GET /item-create.php` |
| 등록 | `POST /item-create.php` |
| 수정 화면 | `GET /item-edit.php?id=10` |
| 수정 | `POST /item-edit.php` |
| 삭제 확인 | `GET /item-delete.php?id=10` |
| 삭제 | `POST /item-delete.php` |
| 상태 변경 | `POST /item-status.php` |
| 내 상품 | `GET /my-items.php` |
| 가입 화면 / 가입 | `GET /register.php`, `POST /register.php` |
| 로그인 화면 / 로그인 | `GET /login.php`, `POST /login.php` |
| 로그아웃 | `POST /logout.php` |

`GET /`는 `DirectoryIndex`가 `index.php`면 목록과 같다. 폼 action에는 `index.php`처럼 파일 이름을 적는다.

P1 목록 예: `GET /index.php?game_id=1&q=포션&sort=price_asc&page=2`

## 8. 클래스와 오토로드

클래스와 네임스페이스, Composer 오토로드는 사용하지 않는다. 파일당 함수 모음이면 초기에 읽는 경로가 짧다. 나중에 클래스로 옮길 여지를 README의 개선 항목에 적되, 이번 구현 범위에는 넣지 않는다.
