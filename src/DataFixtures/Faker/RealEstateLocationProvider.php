<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures\Faker;

use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Base;

final class RealEstateLocationProvider extends Base
{
    private const COUNTRIES = [
        'fr' => [
            'locale' => 'fr_FR',
            'pays' => 'France',
            'cities' => [
                'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
                'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille',
                'Rennes', 'Reims', 'Saint-Étienne', 'Toulon', 'Grenoble',
                'Dijon', 'Angers', 'Nîmes', 'Villeurbanne', 'Clermont-Ferrand',
                'Le Mans', 'Aix-en-Provence', 'Brest', 'Tours', 'Amiens',
                'Limoges', 'Annecy', 'Perpignan', 'Boulogne-Billancourt', 'Metz',
                'Besançon', 'Orléans', 'Saint-Denis', 'Rouen', 'Montreuil',
                'Argenteuil', 'Mulhouse', 'Caen', 'Nancy', 'Roubaix',
                'Tourcoing', 'Nanterre', 'Avignon', 'Vitry-sur-Seine', 'Créteil',
                'Dunkerque', 'Poitiers', 'Asnières-sur-Seine', 'Courbevoie', 'Versailles',
                'Colombes', 'Aulnay-sous-Bois', 'Rueil-Malmaison', 'Pau', 'Aubervilliers',
                'Champigny-sur-Marne', 'Antibes', 'Saint-Maur-des-Fossés', 'La Rochelle', 'Calais',
                'Cannes', 'Béziers', 'Colmar', 'Drancy', 'Mérignac',
                'Ajaccio', 'Bourges', 'Saint-Nazaire', 'Valence', 'Quimper',
                'Noisy-le-Grand', 'Levallois-Perret', 'Vénissieux', 'Cergy', 'Pessac',
                'Ivry-sur-Seine', 'Troyes', 'Lorient', 'Chambéry', 'Niort',
                'Montauban', 'Sarcelles', 'Villejuif', 'Saint-Quentin', 'Beauvais',
                'Hyères', 'Cholet', 'Épinay-sur-Seine', 'Meaux', 'Fontenay-sous-Bois',
                'Fréjus', 'Arles', 'Vincennes', 'Maisons-Alfort', 'Chalon-sur-Saône',
                'Albi', 'Narbonne', 'Saint-Brieuc', 'Sète', 'Bayonne',
            ],
        ],

        'usa' => [
            'locale' => 'en_US',
            'pays' => 'États-Unis',
            'cities' => [
                'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
                'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose',
                'Austin', 'Jacksonville', 'Fort Worth', 'Columbus', 'Charlotte',
                'San Francisco', 'Indianapolis', 'Seattle', 'Denver', 'Washington',
                'Boston', 'El Paso', 'Nashville', 'Detroit', 'Oklahoma City',
                'Portland', 'Las Vegas', 'Memphis', 'Louisville', 'Baltimore',
                'Milwaukee', 'Albuquerque', 'Tucson', 'Fresno', 'Sacramento',
                'Mesa', 'Kansas City', 'Atlanta', 'Omaha', 'Colorado Springs',
                'Raleigh', 'Miami', 'Virginia Beach', 'Oakland', 'Minneapolis',
                'Tulsa', 'Arlington', 'Tampa', 'New Orleans', 'Wichita',
                'Cleveland', 'Bakersfield', 'Aurora', 'Anaheim', 'Honolulu',
                'Santa Ana', 'Riverside', 'Corpus Christi', 'Lexington', 'Henderson',
                'Stockton', 'Saint Paul', 'Cincinnati', 'St. Louis', 'Pittsburgh',
                'Greensboro', 'Lincoln', 'Anchorage', 'Plano', 'Orlando',
                'Irvine', 'Newark', 'Durham', 'Chula Vista', 'Toledo',
                'Fort Wayne', 'St. Petersburg', 'Laredo', 'Jersey City', 'Chandler',
                'Madison', 'Lubbock', 'Scottsdale', 'Reno', 'Buffalo',
                'Gilbert', 'Glendale', 'North Las Vegas', 'Winston-Salem', 'Chesapeake',
                'Norfolk', 'Fremont', 'Garland', 'Irving', 'Hialeah',
                'Richmond', 'Boise', 'Spokane', 'Baton Rouge', 'Tacoma',
            ],
        ],

        'gb' => [
            'locale' => 'en_GB',
            'pays' => 'Royaume-Uni',
            'cities' => [
                'London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool',
                'Leeds', 'Sheffield', 'Edinburgh', 'Bristol', 'Cardiff',
                'Leicester', 'Coventry', 'Nottingham', 'Newcastle upon Tyne', 'Belfast',
                'Brighton', 'Hull', 'Plymouth', 'Stoke-on-Trent', 'Wolverhampton',
                'Derby', 'Swansea', 'Southampton', 'Portsmouth', 'York',
                'Aberdeen', 'Dundee', 'Oxford', 'Cambridge', 'Norwich',
                'Exeter', 'Bath', 'Reading', 'Milton Keynes', 'Luton',
                'Northampton', 'Peterborough', 'Ipswich', 'Colchester', 'Chelmsford',
                'Southend-on-Sea', 'Canterbury', 'Maidstone', 'Dover', 'Slough',
                'Watford', 'Croydon', 'Woking', 'Guildford', 'Bournemouth',
                'Poole', 'Weymouth', 'Swindon', 'Gloucester', 'Cheltenham',
                'Worcester', 'Hereford', 'Shrewsbury', 'Telford', 'Chester',
                'Warrington', 'Preston', 'Blackburn', 'Blackpool', 'Lancaster',
                'Carlisle', 'Durham', 'Sunderland', 'Middlesbrough', 'Darlington',
                'Harrogate', 'Bradford', 'Huddersfield', 'Halifax', 'Wakefield',
                'Doncaster', 'Rotherham', 'Barnsley', 'Grimsby', 'Lincoln',
                'Mansfield', 'Wigan', 'Bolton', 'Oldham', 'Rochdale',
                'Stockport', 'Salford', 'Bury', 'Newport', 'Wrexham',
                'Bangor', 'Inverness', 'Stirling', 'Perth', 'Paisley',
                'Falkirk', 'Kirkcaldy', 'Dumfries', 'Ayr', 'Lisburn',
            ],
        ],

        'ru' => [
            'locale' => 'ru_RU',
            'pays' => 'Russie',
            'cities' => [
                'Moscou', 'Saint-Pétersbourg', 'Novossibirsk', 'Ekaterinbourg', 'Kazan',
                'Nijni Novgorod', 'Tcheliabinsk', 'Samara', 'Omsk', 'Rostov-sur-le-Don',
                'Oufa', 'Krasnoïarsk', 'Perm', 'Voronej', 'Volgograd',
                'Krasnodar', 'Saratov', 'Tioumen', 'Togliatti', 'Ijevsk',
                'Barnaoul', 'Oulianovsk', 'Irkoutsk', 'Khabarovsk', 'Iaroslavl',
                'Vladivostok', 'Makhatchkala', 'Tomsk', 'Orenbourg', 'Kemerovo',
                'Novokouznetsk', 'Riazan', 'Astrakhan', 'Naberejnye Tchelny', 'Penza',
                'Lipetsk', 'Kirov', 'Tcheboksary', 'Toula', 'Kaliningrad',
                'Koursk', 'Stavropol', 'Oulan-Oude', 'Sotchi', 'Tver',
                'Magnitogorsk', 'Ivanovo', 'Briansk', 'Belgorod', 'Sourgout',
                'Vladimir', 'Nijni Taguil', 'Tchita', 'Arkhangelsk', 'Kalouga',
                'Smolensk', 'Voljski', 'Saransk', 'Tcherepovets', 'Vologda',
                'Iakoutsk', 'Kourgan', 'Orel', 'Podolsk', 'Grozny',
                'Tambov', 'Sterlitamak', 'Petrozavodsk', 'Kostroma', 'Nijnevartovsk',
                'Novorossiisk', 'Iochkar-Ola', 'Khimki', 'Taganrog', 'Komsomolsk-sur-l’Amour',
                'Syktyvkar', 'Naltchik', 'Chakhty', 'Dzerjinsk', 'Orsk',
                'Bratsk', 'Angarsk', 'Blagovechtchensk', 'Stary Oskol', 'Veliki Novgorod',
                'Pskov', 'Mourmansk', 'Balachikha', 'Rybinsk', 'Biïsk',
                'Prokopievsk', 'Armavir', 'Korolev', 'Mytichtchi', 'Lioubertsy',
                'Elektrostal', 'Elista', 'Nakhodka', 'Norilsk', 'Roubtsovsk',
            ],
        ],

        'de' => [
            'locale' => 'de_DE',
            'pays' => 'Allemagne',
            'cities' => [
                'Berlin', 'Hambourg', 'Munich', 'Cologne', 'Francfort-sur-le-Main',
                'Stuttgart', 'Düsseldorf', 'Leipzig', 'Dortmund', 'Essen',
                'Brême', 'Dresde', 'Hanovre', 'Nuremberg', 'Duisbourg',
                'Bochum', 'Wuppertal', 'Bielefeld', 'Bonn', 'Münster',
                'Karlsruhe', 'Mannheim', 'Augsbourg', 'Wiesbaden', 'Gelsenkirchen',
                'Mönchengladbach', 'Braunschweig', 'Chemnitz', 'Kiel', 'Aix-la-Chapelle',
                'Halle', 'Magdebourg', 'Fribourg-en-Brisgau', 'Krefeld', 'Lübeck',
                'Oberhausen', 'Erfurt', 'Mayence', 'Rostock', 'Kassel',
                'Hagen', 'Potsdam', 'Sarrebruck', 'Hamm', 'Ludwigshafen',
                'Mülheim an der Ruhr', 'Oldenbourg', 'Osnabrück', 'Leverkusen', 'Heidelberg',
                'Darmstadt', 'Solingen', 'Ratisbonne', 'Herne', 'Neuss',
                'Paderborn', 'Ingolstadt', 'Offenbach-sur-le-Main', 'Fürth', 'Ulm',
                'Heilbronn', 'Pforzheim', 'Wolfsbourg', 'Göttingen', 'Bottrop',
                'Reutlingen', 'Coblence', 'Bremerhaven', 'Recklinghausen', 'Bergisch Gladbach',
                'Iéna', 'Erlangen', 'Remscheid', 'Trèves', 'Salzgitter',
                'Moers', 'Siegen', 'Hildesheim', 'Cottbus', 'Kaiserslautern',
                'Gütersloh', 'Schwerin', 'Witten', 'Hanau', 'Gera',
                'Esslingen am Neckar', 'Ludwigsburg', 'Iserlohn', 'Düren', 'Tübingen',
                'Flensburg', 'Ratingen', 'Villingen-Schwenningen', 'Constance', 'Worms',
                'Velbert', 'Minden', 'Norderstedt', 'Bamberg', 'Marbourg',
            ],
        ],

        'ma' => [
            'locale' => 'fr_FR',
            'pays' => 'Maroc',
            'cities' => [
                'Casablanca', 'Rabat', 'Fès', 'Marrakech', 'Agadir',
                'Tanger', 'Meknès', 'Oujda', 'Kénitra', 'Tétouan',
                'Safi', 'Mohammedia', 'El Jadida', 'Béni Mellal', 'Nador',
                'Taza', 'Khouribga', 'Settat', 'Larache', 'Ksar El Kébir',
                'Khémisset', 'Guelmim', 'Berrechid', 'Oued Zem', 'Fquih Ben Salah',
                'Taourirt', 'Berkane', 'Sidi Slimane', 'Errachidia', 'Guercif',
                'Ouarzazate', 'Tiflet', 'Essaouira', 'Dakhla', 'Taroudant',
                'Sefrou', 'Youssoufia', 'Fnideq', 'Sidi Kacem', 'Tiznit',
                'Tan-Tan', 'Sidi Bennour', 'Martil', 'Azrou', 'Midelt',
                'Skhirat', 'Ouezzane', 'Chefchaouen', 'El Kelaâ des Sraghna', 'Boujdour',
                'Zagora', 'Tinghir', 'Ifrane', 'Témara', 'Salé',
                'Aït Melloul', 'Laâyoune', 'Ksar Sghir', 'Bouznika', 'Ben Guerir',
                'Souk El Arbaa', 'Jerada', 'Azemmour', 'Demnate', 'Imzouren',
                'Sidi Ifni', 'Bir Jdid', 'Had Soualem', 'Assilah', 'M’diq',
                'Al Hoceïma', 'Benslimane', 'Sidi Yahya El Gharb', 'Tamesna', 'Ain Harrouda',
                'Mechra Bel Ksiri', 'Béni Ansar', 'Imintanoute', 'Taounate', 'Chichaoua',
                'Sidi Rahal', 'Bouarfa', 'El Hajeb', 'Moulay Bousselham', 'Aourir',
                'Sidi Bouzid', 'Harhoura', 'Ain Aouda', 'Souk Sebt', 'Kelaat M’Gouna',
                'Missour', 'Sidi Allal El Bahraoui', 'Boumalne Dadès', 'Talsint', 'Oulad Teima',
                'Figuig', 'Bhalil', 'Moulay Idriss Zerhoun', 'Tahannaout', 'Tarfaya',
            ],
        ],
    ];

    public function realEstateCountryCode(): string
    {
        return self::randomElement(array_keys(self::COUNTRIES));
    }

    public function realEstateLocation(?string $countryCode = null): array
    {
        $countryCode = $this->normalizeCountryCode($countryCode);

        $country = self::COUNTRIES[$countryCode];

        /** @var string $city */
        $city = self::randomElement($country['cities']);

        $countryFaker = Factory::create($country['locale']);

        $adresse = $this->generateStreetAddress($countryFaker);
        $codePostal = $this->generatePostcode($countryCode, $countryFaker);

        return [
            'countryCode' => $countryCode,
            'locale' => $country['locale'],
            'pays' => $country['pays'],
            'ville' => $city,
            'adresse' => $adresse,
            'codePostal' => $codePostal,
            'fullAddress' => \sprintf(
                '%s, %s %s, %s',
                $adresse,
                $codePostal,
                $city,
                $country['pays']
            ),
        ];
    }

    private function normalizeCountryCode(?string $countryCode): string
    {
        if (null === $countryCode || '' === mb_trim($countryCode)) {
            return $this->realEstateCountryCode();
        }

        $countryCode = mb_strtolower(mb_trim($countryCode));

        $countryCode = match ($countryCode) {
            'us', 'usa', 'united-states', 'etats-unis', 'états-unis' => 'usa',
            'uk', 'gb', 'en', 'england', 'royaume-uni' => 'gb',
            'fr', 'france' => 'fr',
            'ru', 'russia', 'russie' => 'ru',
            'de', 'germany', 'allemagne' => 'de',
            'ma', 'morocco', 'maroc' => 'ma',
            default => $countryCode,
        };

        return \array_key_exists($countryCode, self::COUNTRIES) ? $countryCode : 'fr';
    }

    private function generateStreetAddress(Generator $faker): string
    {
        try {
            return mb_trim((string) $faker->streetAddress());
        } catch (\Throwable) {
            return mb_trim($faker->buildingNumber().' '.$faker->streetName());
        }
    }

    private function generatePostcode(string $countryCode, Generator $faker): string
    {
        if ('ma' === $countryCode) {
            return (string) $faker->numberBetween(10000, 99999);
        }

        try {
            return mb_trim((string) $faker->postcode());
        } catch (\Throwable) {
            return (string) $faker->numberBetween(10000, 99999);
        }
    }
}
