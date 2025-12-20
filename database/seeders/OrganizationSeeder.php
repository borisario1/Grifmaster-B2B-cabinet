<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем пользователей из дампа
        $userIds = [100, 414, 415, 416, 422];
        foreach ($userIds as $id) {
            User::firstOrCreate(['id' => $id], [
                'name' => "User $id",
                'email' => "user$id@grifmaster.ru",
                'password' => Hash::make('password'),
            ]);
        }

        // 2. Полный список организаций (16 записей)
        $organizations = [
            ['id' => 1, 'user_id' => 100, 'name' => 'ООО АВРОРА', 'inn' => '30755504', 'kpp' => '230754551', 'type' => 'org', 'is_deleted' => true],
            ['id' => 2, 'user_id' => 100, 'name' => 'ИП Васьков Иван Иванович', 'inn' => '716548005', 'kpp' => '315748955', 'type' => 'ip', 'is_deleted' => false],
            ['id' => 3, 'user_id' => 100, 'name' => 'ООО КОТЫ', 'inn' => '5021145874', 'kpp' => '144008576', 'type' => 'org', 'is_deleted' => true],
            ['id' => 4, 'user_id' => 100, 'name' => '111', 'inn' => '222', 'kpp' => '', 'type' => 'org', 'is_deleted' => true],
            ['id' => 7, 'user_id' => 100, 'name' => 'ООО "МОИК"', 'inn' => '5029205458', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1155029011829', 'is_deleted' => false],
            ['id' => 8, 'user_id' => 100, 'name' => 'ИП Григорьев Владимир Владимирович', 'inn' => '502912649382', 'kpp' => null, 'type' => 'ip', 'ogrn' => '323508100213862', 'is_deleted' => false],
            ['id' => 14, 'user_id' => 100, 'name' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '502920258760', 'kpp' => null, 'type' => 'ip', 'ogrn' => '1255000059907', 'is_deleted' => true],
            ['id' => 15, 'user_id' => 100, 'name' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5550005550', 'kpp' => '144008576', 'type' => 'org', 'ogrn' => '1155029011829', 'is_deleted' => true],
            ['id' => 16, 'user_id' => 100, 'name' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5029293944', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1255000059907', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1', 'is_deleted' => true],
            ['id' => 17, 'user_id' => 414, 'name' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5029293944', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1255000059907', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1', 'is_deleted' => false],
            ['id' => 18, 'user_id' => 415, 'name' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5029293944', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1255000059907', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1', 'is_deleted' => false],
            ['id' => 19, 'user_id' => 416, 'name' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5029293944', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1255000059907', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1', 'is_deleted' => true],
            ['id' => 20, 'user_id' => 100, 'name' => 'ИП Бессонов Максим Игоревич', 'inn' => '502210472705', 'kpp' => null, 'type' => 'ip', 'ogrn' => '318502200027993', 'address' => '140400, Московская обл, г Коломна', 'is_deleted' => false],
            ['id' => 21, 'user_id' => 100, 'name' => 'Карагандов Алексей Алексеевич', 'inn' => '509878555647', 'kpp' => null, 'type' => 'ip', 'ogrn' => '1255000059907', 'is_deleted' => false],
            ['id' => 22, 'user_id' => 422, 'name' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'inn' => '5029293944', 'kpp' => '502901001', 'type' => 'org', 'ogrn' => '1255000059907', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1', 'is_deleted' => true],
            ['id' => 23, 'user_id' => 100, 'name' => 'ООО "ЭЛЕГИЯ"', 'inn' => '5050110093', 'kpp' => '505001001', 'type' => 'org', 'ogrn' => '1145050001568', 'address' => '141100, Московская обл, г Щёлково, Пролетарский пр-кт, д 4 к 1, помещ 1/26', 'is_deleted' => false],
        ];

        foreach ($organizations as $org) {
            Organization::updateOrCreate(['id' => $org['id']], $org);
        }

        // 3. Полные данные из b2b_organization_info (Dadata JSON и др.)
        $infos = [
            [
                'id' => 1, 'organization_id' => 7, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1155029011829', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "МОСКОВСКАЯ ОБЛАСТНАЯ ИНЖЕНЕРНАЯ КОМПАНИЯ"',
                'name_short' => 'ООО "МОИК"', 'address' => '141002, Московская обл, г Мытищи, ул Колпакова, д 2 литера е, помещ 301',
                'registered_at' => '2015-11-25 00:00:00',
                'dadata_raw' => json_decode('{"qc": null, "hid": "0728b04a1b48455b21f22476316b2f0ab16f38fee81108a09d09a70dbe7b9440", "inn": "5029205458", "kpp": "502901001", "name": {"full": "МОСКОВСКАЯ ОБЛАСТНАЯ ИНЖЕНЕРНАЯ КОМПАНИЯ", "short": "МОИК"}}', true),
            ],
            [
                'id' => 2, 'organization_id' => 8, 'status' => 'ACTIVE', 'branch_type' => null, 'opf' => 'ИП', 'ogrn' => '323508100213862', 'kpp' => null,
                'name_full' => 'Индивидуальный предприниматель Григорьев Владимир Владимирович',
                'name_short' => 'ИП Григорьев Владимир Владимирович', 'address' => '141000, Московская обл, г Мытищи',
                'registered_at' => '2023-04-18 00:00:00',
                'dadata_raw' => json_decode('{"qc": null, "fio": {"name": "Владимир", "surname": "Григорьев"}, "inn": "502912649382", "ogrn": "323508100213862"}', true),
            ],
            [
                'id' => 6, 'organization_id' => 16, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1255000059907', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"',
                'name_short' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1',
                'registered_at' => '2025-06-25 00:00:00',
                'dadata_raw' => json_decode('{"inn": "5029293944", "kpp": "502901001", "ogrn": "1255000059907", "state": {"status": "ACTIVE"}}', true),
            ],
            [
                'id' => 7, 'organization_id' => 17, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1255000059907', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"',
                'name_short' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1',
                'registered_at' => '2025-06-25 00:00:00',
                'dadata_raw' => json_decode('{"inn": "5029293944", "kpp": "502901001", "ogrn": "1255000059907"}', true),
            ],
            [
                'id' => 8, 'organization_id' => 18, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1255000059907', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"',
                'name_short' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1',
                'registered_at' => '2025-06-25 00:00:00',
                'dadata_raw' => json_decode('{"inn": "5029293944", "kpp": "502901001", "ogrn": "1255000059907"}', true),
            ],
            [
                'id' => 9, 'organization_id' => 19, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1255000059907', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"',
                'name_short' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1',
                'registered_at' => '2025-06-25 00:00:00',
                'dadata_raw' => json_decode('{"inn": "5029293944", "kpp": "502901001", "ogrn": "1255000059907"}', true),
            ],
            [
                'id' => 10, 'organization_id' => 20, 'status' => 'ACTIVE', 'branch_type' => null, 'opf' => 'ИП', 'ogrn' => '318502200027993', 'kpp' => null,
                'name_full' => 'Индивидуальный предприниматель Бессонов Максим Игоревич',
                'name_short' => 'ИП Бессонов Максим Игоревич', 'address' => '140400, Московская обл, г Коломна',
                'registered_at' => '2018-08-17 00:00:00',
                'dadata_raw' => json_decode('{"inn": "502210472705", "ogrn": "318502200027993", "fio": {"name": "Максим"}}', true),
            ],
            [
                'id' => 11, 'organization_id' => 22, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1255000059907', 'kpp' => '502901001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ПАНДА ГРУПП ЛИМИТЕД"',
                'name_short' => 'ООО "ПАНДА ГРУПП ЛИМИТЕД"', 'address' => '141006, Московская обл, г Мытищи, ул 1-я Пролетарская, д 3, кв 1',
                'registered_at' => '2025-06-25 00:00:00',
                'dadata_raw' => json_decode('{"inn": "5029293944", "ogrn": "1255000059907"}', true),
            ],
            [
                'id' => 12, 'organization_id' => 23, 'status' => 'ACTIVE', 'branch_type' => 'MAIN', 'opf' => 'ООО', 'ogrn' => '1145050001568', 'kpp' => '505001001',
                'name_full' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ЭЛЕГИЯ"',
                'name_short' => 'ООО "ЭЛЕГИЯ"', 'address' => '141100, Московская обл, г Щёлково, Пролетарский пр-кт, д 4 к 1, помещ 1/26',
                'registered_at' => '2014-04-07 00:00:00',
                'dadata_raw' => json_decode('{"qc": null, "hid": "128bf07c5150c79d550f29618e8174fd1ac65af1e22814685a51441c158ab4ac", "inn": "5050110093", "ogrn": "1145050001568"}', true),
            ],
        ];

        foreach ($infos as $info) {
            OrganizationInfo::updateOrCreate(['id' => $info['id']], $info);
        }
    }
}