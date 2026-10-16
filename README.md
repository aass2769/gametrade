# GameTrade

게임 아이템 판매 정보를 등록하고 조회하는 Laravel 연습 프로젝트다. 결제, 포인트, 채팅, 이미지 업로드는 없다. 판매자가 거래 상태를 표시하는 곳까지가 범위다.

## 개발 목적

Laravel에서 요청 처리, 폼 검증, 세션 인증, Eloquent, 작성자 권한, REST API를 한 프로젝트로 보여 준다. 화면은 Blade이고, 같은 규칙은 `/api/v1`에서 Sanctum 토큰으로 동작한다.

## 기술 스택

이 환경에서 확인한 버전이다.

- PHP 8.5.11
- Laravel 13
- MySQL 8.4 (Docker, `mysql` 드라이버)
- Blade, HTML, CSS
- Laravel Sanctum

데이터베이스는 이 저장소의 `docker-compose.yml`이 띄우는 MySQL 컨테이너다. 세션은 브라우저를 닫으면 끝나며, 로컬 HTTP라서 세션 쿠키의 `Secure` 플래그는 켜지 않는다.

목록은 최신순, 가격 낮은 순, 가격 높은 순을 고를 수 있고, 한 페이지에 10건씩 나온다. 반응형 레이아웃은 아직 없다.

## 실행 방법

Docker가 실행 중이어야 한다. 맥의 3306번 포트를 다른 데이터베이스가 쓰고 있으면 컨테이너가 켜지지 않는다.

```bash
composer install
cp .env.example .env
docker compose up -d
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8080
```

브라우저에서 `http://127.0.0.1:8080/` 을 연다.

컨테이너가 처음 켜질 때 `gametrade` 데이터베이스를 만든다. Laravel과 DBeaver는 `127.0.0.1:3306`으로 접속한다. 계정은 `gametrade`, 비밀번호는 `gametrade`다. 루트 비밀번호는 `gametrade-root`다. 이 값들은 로컬 연습용이며 `.env`에 있다. `.env`는 Git에 넣지 않는다.

`APP_DEBUG`는 로컬 확인이 끝나면 `false`로 둔다. 오류 SQL은 화면에 나오지 않고 `storage/logs/laravel.log`에 남는다.

샘플 계정은 로컬 확인용이다.

| 아이디 | 비밀번호 |
| --- | --- |
| seller1 ~ seller12 | seller1234 |

## API

기본 경로는 `/api/v1`이다. 목록과 상세, 게임 목록은 로그인 없이 볼 수 있다. 등록, 수정, 삭제, 상태 변경, 내 판매 상품은 `Authorization: Bearer` 토큰이 필요하다. 토큰은 `POST /api/v1/register` 또는 `POST /api/v1/login`으로 받는다.

## 보안 처리

- 조회와 저장은 Eloquent 바인딩을 사용한다. 검색어의 `%`와 `_`는 빼서 LIKE 패턴으로 쓰이지 않게 한다.
- 비밀번호는 `hashed` 캐스트로 저장하고 `Hash::check`로 확인한다. 없는 아이디도 같은 실패 문구를 반환한다.
- Blade가 출력 값을 이스케이프한다.
- 웹의 변경 요청은 CSRF 토큰을 검사한다. 로그아웃은 POST만 받는다.
- 수정, 삭제, 상태 변경은 Policy에서 작성자를 확인하고, 내용 수정과 삭제는 판매 중일 때만 SQL 조건으로 실행한다.
- 상태 변경은 트랜잭션과 행 잠금 안에서 현재 상태를 다시 읽는다.

## 테스트 결과

2026-10-15에 PHPUnit feature 테스트 15건이 통과했다. 같은 날 `http://127.0.0.1:8080`에서 목록, 상세, 검색, 회원가입, 로그인, 권한, 상태 변경, CSRF, `/api/v1` 토큰 흐름을 HTTP로 확인했다. 브라우저에서 화면 너비를 바꿔 보는 확인은 하지 않았다.

좁은 화면은 아직 구현하지 않았다.
