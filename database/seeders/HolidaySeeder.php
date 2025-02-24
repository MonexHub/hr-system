<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Holiday::getDefaultHolidays() as $holiday) {
            Holiday::create([
                'name' => $holiday['name'],
                'name_sw' => $holiday['name_sw'],
                'date' => $holiday['date'],
                'type' => $holiday['type'],
                'is_recurring' => $holiday['is_recurring'],
                'status' => 'active',
                'send_notification' => true,
                'description' => $this->getDefaultDescription($holiday['name'], 'en'),
                'description_sw' => $this->getDefaultDescription($holiday['name'], 'sw'),
            ]);
        }
    }

    private function getDefaultDescription($holidayName, $language): string
    {
        $descriptions = [
            'New Year\'s Day' => [
                'en' => 'Celebration of the new year.',
                'sw' => 'Sherehe ya mwaka mpya.',
            ],
            'Zanzibar Revolution Day' => [
                'en' => 'Commemorates the overthrow of the Sultan of Zanzibar in 1964.',
                'sw' => 'Kumbukumbu ya mapinduzi ya Zanzibar ya mwaka 1964.',
            ],
            'Union Day' => [
                'en' => 'Celebrates the union of Tanganyika and Zanzibar in 1964.',
                'sw' => 'Sherehe ya muungano wa Tanganyika na Zanzibar mwaka 1964.',
            ],
            'Workers Day' => [
                'en' => 'International Workers\' Day celebration.',
                'sw' => 'Sherehe ya siku ya wafanyakazi duniani.',
            ],
            'Saba Saba Day' => [
                'en' => 'Tanzania National Day of Trade and Industry.',
                'sw' => 'Siku ya Biashara na Viwanda Tanzania.',
            ],
            'Nane Nane Day' => [
                'en' => 'Farmers\' Day celebrations.',
                'sw' => 'Sherehe za siku ya wakulima.',
            ],
            'Nyerere Day' => [
                'en' => 'Commemorates the death of Julius Nyerere, the first President of Tanzania.',
                'sw' => 'Kumbukumbu ya kifo cha Julius Nyerere, Rais wa kwanza wa Tanzania.',
            ],
            'Independence Day' => [
                'en' => 'Celebrates Tanzania\'s independence from British rule in 1961.',
                'sw' => 'Sherehe za uhuru wa Tanzania kutoka utawala wa Waingereza mwaka 1961.',
            ],
            'Christmas Day' => [
                'en' => 'Christian celebration of the birth of Jesus Christ.',
                'sw' => 'Sherehe ya Kuzaliwa kwa Yesu Kristo.',
            ],
            'Boxing Day' => [
                'en' => 'Public holiday following Christmas Day.',
                'sw' => 'Siku ya mapumziko baada ya Krismas.',
            ],
            'Eid al-Fitr' => [
                'en' => 'Islamic holiday marking the end of Ramadan.',
                'sw' => 'Sikukuu ya Kiislamu inayoashiria mwisho wa mfungo wa Ramadhan.',
            ],
            'Eid al-Adha' => [
                'en' => 'Islamic festival of sacrifice.',
                'sw' => 'Sikukuu ya Kiislamu ya Kuchinja.',
            ],
            'Good Friday' => [
                'en' => 'Christian commemoration of the crucifixion of Jesus Christ.',
                'sw' => 'Kumbukumbu ya Kikristo ya kusulubiwa kwa Yesu Kristo.',
            ],
            'Easter Monday' => [
                'en' => 'Christian celebration following Easter Sunday.',
                'sw' => 'Sherehe ya Kikristo inayofuata Jumapili ya Pasaka.',
            ],
            'Karume Day' => [
                'en' => 'Commemorates the assassination of Zanzibar\'s first President, Abeid Karume.',
                'sw' => 'Kumbukumbu ya kuuawa kwa Rais wa kwanza wa Zanzibar, Abeid Karume.',
            ],
        ];

        return $descriptions[$holidayName][$language] ?? '';
    }
}
