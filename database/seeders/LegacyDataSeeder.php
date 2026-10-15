<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('LegacyDataSeeder는 MySQL 또는 MariaDB에서만 실행합니다.');
        }

        DB::table('users')->insert($this->rows('users.tsv', function (array $row): array {
            return [
                'id' => (int) $row[0],
                'username' => $row[1],
                'password' => $row[2],
                'created_at' => $row[3],
                'updated_at' => $row[4],
            ];
        }));

        DB::table('games')->insert($this->rows('games.tsv', function (array $row): array {
            return [
                'id' => (int) $row[0],
                'name' => $row[1],
                'created_at' => $row[2],
            ];
        }));

        DB::table('game_servers')->insert($this->rows('game_servers.tsv', function (array $row): array {
            return [
                'id' => (int) $row[0],
                'game_id' => (int) $row[1],
                'name' => $row[2],
                'created_at' => $row[3],
            ];
        }));

        DB::table('items')->insert($this->rows('items.tsv', function (array $row): array {
            return [
                'id' => (int) $row[0],
                'user_id' => (int) $row[1],
                'server_id' => (int) $row[2],
                'item_name' => $row[3],
                'price' => (int) $row[4],
                'quantity' => (int) $row[5],
                'description' => $row[6] === '' ? null : $row[6],
                'status' => $row[7],
                'created_at' => $row[8],
                'updated_at' => $row[9],
            ];
        }));

        foreach (['users', 'games', 'game_servers', 'items'] as $table) {
            $next = (int) DB::table($table)->max('id') + 1;
            DB::statement('ALTER TABLE '.$table.' AUTO_INCREMENT = '.$next);
        }
    }

    /**
     * @param  callable(array<int, string>): array<string, mixed>  $map
     * @return list<array<string, mixed>>
     */
    private function rows(string $filename, callable $map): array
    {
        $path = storage_path('backups/'.$filename);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException($filename.' 백업 파일을 열 수 없습니다.');
        }

        $rows = [];
        $header = true;
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if ($header) {
                $header = false;
                continue;
            }
            if ($row === [null] || $row === []) {
                continue;
            }
            $rows[] = $map($row);
        }
        fclose($handle);

        return $rows;
    }
}
