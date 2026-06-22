<?php

namespace App\DataFixtures;

use App\Entity\LangueParler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LangueParlerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $languages = [
            'fr' => 'français',
            'en' => 'English',
            'es' => 'español',
            'de' => 'Deutsch',
            'it' => 'italiano',
            'pt' => 'português',
            'nl' => 'Nederlands',
            'pl' => 'polski',
            'ru' => 'русский',
            'uk' => 'українська',
            'be' => 'беларуская',
            'ro' => 'română',
            'bg' => 'български',
            'el' => 'Ελληνικά',
            'tr' => 'Türkçe',
            'ar' => 'العربية',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
            'hi' => 'हिन्दी',
            'bn' => 'বাংলা',
            'ur' => 'اردو',
            'fa' => 'فارسی',
            'he' => 'עברית',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'tl' => 'Tagalog',
            'sw' => 'Kiswahili',
            'am' => 'አማርኛ',
            'yo' => 'Yorùbá',
            'ig' => 'Igbo',
            'ha' => 'Hausa',
            'zu' => 'isiZulu',
            'af' => 'Afrikaans',
            'sq' => 'shqip',
            'sr' => 'српски',
            'hr' => 'hrvatski',
            'bs' => 'bosanski',
            'sl' => 'slovenščina',
            'sk' => 'slovenčina',
            'cs' => 'čeština',
            'hu' => 'magyar',
            'fi' => 'suomi',
            'sv' => 'svenska',
            'no' => 'norsk',
            'da' => 'dansk',
            'is' => 'íslenska',
            'ga' => 'Gaeilge',
            'cy' => 'Cymraeg',
            'mt' => 'Malti',
            'et' => 'eesti',
            'lv' => 'latviešu',
            'lt' => 'lietuvių',
            'mk' => 'македонски',
            'ka' => 'ქართული',
            'hy' => 'հայերեն',
            'az' => 'azərbaycan dili',
            'kk' => 'қазақ тілі',
            'uz' => 'o‘zbek',
            'mn' => 'монгол',
            'ne' => 'नेपाली',
            'si' => 'සිංහල',
            'ta' => 'தமிழ்',
            'te' => 'తెలుగు',
            'ml' => 'മലയാളം',
            'kn' => 'ಕನ್ನಡ',
            'mr' => 'मराठी',
            'gu' => 'ગુજરાતી',
            'pa' => 'ਪੰਜਾਬੀ',
            'km' => 'ភាសាខ្មែរ',
            'lo' => 'ລາວ',
            'my' => 'မြန်မာဘာသာ',
            'ps' => 'پښتو',
            'ku' => 'kurdî',
            'so' => 'Soomaali',
            'ti' => 'ትግርኛ',
            'rw' => 'Kinyarwanda',
            'mg' => 'Malagasy',
            'ht' => 'Kreyòl ayisyen',
            'ca' => 'català',
            'eu' => 'euskara',
            'gl' => 'galego',
        ];

        foreach ($languages as $code => $name) {
            $langue = new LangueParler();
            $langue->setCode($code);
            $langue->setName($name);

            $manager->persist($langue);

            $this->addReference('langue_parler_' . $code, $langue);
        }

        $manager->flush();
    }
}