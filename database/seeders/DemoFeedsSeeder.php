<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

/**
 * Five hand-curated example vendors that every fresh seed ships with.
 *
 * Each vendor is fully built, user row, vendor row, vendor.yaml,
 * pages/home.yaml and the eight standard-feed component files. The
 * vendors cover the FeedAI target audience: street-food, transport,
 * wellness, cultural tours, and water-based tourism, each with a
 * distinct name, voice and price point so the marketing showcase
 * actually feels like a marketplace, not a copy-paste.
 *
 * Translations are NOT generated here, run
 *   `artisan feedai:backfill-translations` afterwards (or click in the
 *   dashboard) to populate translations/{de,th}/ folders.
 */
class DemoFeedsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->vendors() as $vendor) {
            $this->createVendor($vendor);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function vendors(): array
    {
        return [
            // ------------------------------------------------------------
            // 1. Khao San Coffee Lab, third-wave café, EN
            // ------------------------------------------------------------
            [
                'slug' => 'khao-san-coffee',
                'name' => 'Khao San Coffee Lab',
                'locale' => 'en',
                'owner_name' => 'Ploy & Nik',
                'email' => 'ploy@khaosancoffee.test',
                'accent_color' => '#3F2C20',
                'promptpay' => '0812222111',
                'hero' => [
                    'image' => 'https://images.unsplash.com/photo-1772993629858-5ca090cf226a?w=1200&q=80',
                    'title' => 'Khao San Coffee Lab',
                    'location' => 'Khao San Road · Bangkok',
                    'rating' => '4.9 (412)',
                    'body' => 'Slow-pulled espresso and 12-hour cold brew, two doors down from the chaos. Roasted at the back, served at the bar.',
                ],
                'about' => "Ploy and Nik opened the Lab in **2019** after roasting on Ploy's grandmother's gas stove for three frustrating years.\n\nEverything is from Thailand, beans from family farms in the Chiang Rai highlands, ceramic cups thrown in Lampang, oat milk from a cooperative outside Chiang Mai. We roast twice a week, never store-bought.\n\nIf you want espresso the way Italians drink it, sit at the bar. If you want it the way Thai locals drink it, sweet, iced, layered with coconut, Nik will make you one without judgement.",
                'menu' => [
                    'section_label' => 'COFFEE & DRINKS',
                    'items' => [
                        ['name' => 'Slow Cold Brew', 'price' => '120 THB', 'description' => '12-hour drip, Doi Chaang honey-process beans, served black.', 'image' => 'https://images.unsplash.com/photo-1549652127-2e5e59e86a7a?w=800&q=80'],
                        ['name' => 'Coconut Iced Latte', 'price' => '110 THB', 'description' => 'Double espresso, ice, condensed coconut milk, local way.', 'image' => 'https://images.unsplash.com/photo-1595000453467-15d064f058c8?w=800&q=80'],
                        ['name' => 'Espresso Doppio', 'price' => '85 THB', 'description' => 'Two ristretto shots from our house Chiang Rai blend.', 'image' => 'https://images.unsplash.com/photo-1705952285570-113e76f63fb0?w=800&q=80'],
                        ['name' => 'Single-Origin Pour-Over', 'price' => '150 THB', 'description' => 'V60, rotating bean, 3-minute steep, ceramic dripper.', 'image' => 'https://images.unsplash.com/photo-1541469406036-71229832e06e?w=800&q=80'],
                        ['name' => 'Affogato', 'price' => '140 THB', 'description' => 'Espresso poured tableside over local vanilla bean ice cream.', 'image' => 'https://images.unsplash.com/photo-1638543284847-3a6bed3e1689?w=800&q=80'],
                    ],
                ],
                'opening_hours' => [
                    'note' => 'Closed Mondays, that\'s when we roast.',
                    'hours' => [
                        ['days' => 'Tue–Sat', 'time' => '08:00–18:00'],
                        ['days' => 'Sun', 'time' => '09:00–16:00'],
                        ['days' => 'Mon', 'time' => 'Closed'],
                    ],
                ],
                'contact_buttons' => [
                    ['channel' => 'whatsapp', 'value' => '+66812222111', 'label' => 'WhatsApp'],
                    ['channel' => 'line', 'value' => 'khaosancoffee', 'label' => 'LINE'],
                    ['channel' => 'facebook', 'value' => 'khaosancoffeelab', 'label' => 'Facebook'],
                ],
                'cta' => [
                    'title' => 'Want our beans at home?',
                    'body' => 'Drop us a message, we pack 200g bags fresh on the morning you pick up.',
                    'button_label' => 'Reserve a bag',
                    'button_url' => '#contact-form',
                ],
                'contact_form' => [
                    'title' => 'Message the Lab',
                    'intro' => 'Special order, bean question, opening-hours doubt, we read everything in English, Thai or German. Reply usually same day.',
                ],
            ],

            // ------------------------------------------------------------
            // 2. Niran's Tuk-Tuk Adventures, private tuk-tuk tours, EN
            // ------------------------------------------------------------
            [
                'slug' => 'niran-tuktuk',
                'name' => "Niran's Tuk-Tuk Adventures",
                'locale' => 'en',
                'owner_name' => 'Niran Petchara',
                'email' => 'niran@niran-tuktuk.test',
                'accent_color' => '#B5483A',
                'promptpay' => '0823344551',
                'hero' => [
                    'image' => 'https://images.unsplash.com/photo-1547730899-ba038ec3b91f?w=1200&q=80',
                    'title' => "Niran's Tuk-Tuk Adventures",
                    'location' => 'Banglamphu · Bangkok',
                    'rating' => '4.97 (188)',
                    'body' => 'Eighteen years behind these handlebars. Hidden temples, back-street noodle stalls, sunset spots no tour bus reaches.',
                ],
                'about' => "Niran was born three streets from where his tuk-tuk parks today. He learned the city's shortcuts from his uncle, who learned them from his uncle.\n\nNo fixed route, no scripted commentary. Tell him what you're curious about, old amulet market, photography on the canals, the best dim sum in Yaowarat, and he'll bend the night to match.\n\n**English fluent, Thai native, German functional.** Always on time. Snacks for you in the side pocket.",
                'menu' => [
                    'section_label' => 'TOURS & TRANSFERS',
                    'items' => [
                        ['name' => 'Old Town Half-Day', 'price' => '1,500 THB', 'description' => '3 hours · Wat Pho, Wat Arun, amulet market, hidden coffee stops.', 'image' => 'https://images.unsplash.com/photo-1613672803979-a6edfc5a179b?w=800&q=80'],
                        ['name' => 'Full-Day Bangkok', 'price' => '2,800 THB', 'description' => '6 hours · temples, klongs, Chinatown, sunset rooftop.', 'image' => 'https://images.unsplash.com/photo-1615619825440-f18cea64b00e?w=800&q=80'],
                        ['name' => 'Night Market & Street Food Crawl', 'price' => '1,800 THB', 'description' => '4 hours · five eat stops, Niran knows the cleanest stalls.', 'image' => 'https://images.unsplash.com/photo-1774703699692-7c4b3b0198b4?w=800&q=80'],
                        ['name' => 'BKK Airport Transfer', 'price' => '1,200 THB', 'description' => 'Suvarnabhumi → hotel, fixed price, no meter games.', 'image' => 'https://images.unsplash.com/photo-1583493870655-34bea2fcdefb?w=800&q=80'],
                    ],
                ],
                'opening_hours' => [
                    'note' => 'Sundays I rest, emergencies, message me anyway.',
                    'hours' => [
                        ['days' => 'Mon–Sat', 'time' => '08:00–22:00'],
                        ['days' => 'Sun', 'time' => 'On request'],
                    ],
                ],
                'contact_buttons' => [
                    ['channel' => 'whatsapp', 'value' => '+66823344551', 'label' => 'WhatsApp'],
                    ['channel' => 'line', 'value' => 'niran.tt', 'label' => 'LINE'],
                    ['channel' => 'call', 'value' => '+66823344551', 'label' => 'Call'],
                ],
                'cta' => [
                    'title' => 'Plan your Bangkok day',
                    'body' => 'Tell me where you\'re staying and what you\'re curious about, I send you a route + price by tomorrow morning.',
                    'button_label' => 'Plan a tour',
                    'button_url' => '#contact-form',
                ],
                'contact_form' => [
                    'title' => 'Message Niran',
                    'intro' => 'Write in any language, we translate both ways. Mention your dates and where you\'re staying.',
                ],
            ],

            // ------------------------------------------------------------
            // 3. Pranee Thai Massage, wellness studio, TH source
            // ------------------------------------------------------------
            [
                'slug' => 'pranee-thai-massage',
                'name' => 'Pranee Thai Massage',
                'locale' => 'th',
                'owner_name' => 'Pranee Suwannarat',
                'email' => 'pranee@pranee-massage.test',
                'accent_color' => '#2D5A3D',
                'promptpay' => '0834455667',
                'hero' => [
                    'image' => 'https://images.unsplash.com/photo-1741522509438-a120c0bb5e88?w=1200&q=80',
                    'title' => 'Pranee Thai Massage',
                    'location' => 'Sukhumvit Soi 11 · Bangkok',
                    'rating' => '4.93 (276)',
                    'body' => 'นวดแผนไทยโบราณ โดยครูประณีต ผู้สำเร็จการศึกษาจากวัดโพธิ์ พร้อมประสบการณ์ 15 ปี',
                ],
                'about' => "ครูประณีตเริ่มเรียนนวดแผนไทยที่วัดโพธิ์ตั้งแต่อายุ 17 ปี\n\nวันนี้ห้องนวดเล็กๆ ในซอย 11 รับลูกค้าเพียง 3 คนต่อรอบ เพื่อให้ทุกการนวดมีสมาธิเต็มที่, ไม่เร่ง ไม่ใช่สูตรสำเร็จ\n\nผ้าและน้ำมันเป็นของคนไทย ออร์แกนิคทั้งหมด **ครูดูแลลูกค้าด้วยมือเปล่าและด้วยใจ** ตามแบบฉบับวัดโพธิ์ดั้งเดิม",
                'menu' => [
                    'section_label' => 'บริการนวด',
                    'items' => [
                        ['name' => 'นวดแผนไทยโบราณ 60 นาที', 'price' => '600 บาท', 'description' => 'นวดกดจุดด้วยมือเปล่า ไม่ใช้น้ำมัน เน้นแก้ปวดเมื่อย', 'image' => 'https://images.unsplash.com/photo-1699523229212-c25a2fadeb12?w=800&q=80'],
                        ['name' => 'นวดแผนไทย 90 นาที', 'price' => '900 บาท', 'description' => 'แบบเต็มร่างกาย ครูใช้เวลาเต็มที่ทุกจุด', 'image' => 'https://images.unsplash.com/photo-1700882304335-34d47c682a4c?w=800&q=80'],
                        ['name' => 'นวดน้ำมันอโรมา 60 นาที', 'price' => '800 บาท', 'description' => 'น้ำมันหอมสกัดจากเลม่อนกราสและตะไคร้ ผ่อนคลายลึก', 'image' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?w=800&q=80'],
                        ['name' => 'นวดเท้า 60 นาที', 'price' => '500 บาท', 'description' => 'กดจุดสะท้อนเท้า ดีสำหรับคนเดินทั้งวัน', 'image' => 'https://images.unsplash.com/photo-1611073615830-9f76902c10fe?w=800&q=80'],
                        ['name' => 'ลูกประคบสมุนไพร 90 นาที', 'price' => '1,200 บาท', 'description' => 'ประคบร้อนด้วยสมุนไพรไทย 12 ชนิด แก้ปวดเรื้อรัง', 'image' => 'https://images.unsplash.com/photo-1775133262667-316bd4d9e5b5?w=800&q=80'],
                    ],
                ],
                'opening_hours' => [
                    'note' => 'แนะนำให้จองล่วงหน้า รับลูกค้าครั้งละ 3 คนเท่านั้น',
                    'hours' => [
                        ['days' => 'จันทร์–เสาร์', 'time' => '10:00–22:00'],
                        ['days' => 'อาทิตย์', 'time' => '12:00–20:00'],
                    ],
                ],
                'contact_buttons' => [
                    ['channel' => 'whatsapp', 'value' => '+66834455667', 'label' => 'WhatsApp'],
                    ['channel' => 'line', 'value' => 'pranee.massage', 'label' => 'LINE'],
                    ['channel' => 'call', 'value' => '+66834455667', 'label' => 'โทร'],
                ],
                'cta' => [
                    'title' => 'จองคิวล่วงหน้า',
                    'body' => 'รับลูกค้าครั้งละ 3 ท่าน ขอแนะนำให้ทักมาก่อนสักวันค่ะ',
                    'button_label' => 'ส่งข้อความหาครู',
                    'button_url' => '#contact-form',
                ],
                'contact_form' => [
                    'title' => 'ส่งข้อความถึงครูประณีต',
                    'intro' => 'เขียนภาษาอะไรก็ได้, เราแปลให้ทั้งสองทาง ส่งวันเวลาที่ต้องการมาได้เลยค่ะ',
                ],
            ],

            // ------------------------------------------------------------
            // 4. Kru Vee Cultural Walks, temple guide, EN
            // ------------------------------------------------------------
            [
                'slug' => 'kru-vee-walks',
                'name' => 'Walking Wat Pho with Kru Vee',
                'locale' => 'en',
                'owner_name' => 'Vee Chayanon',
                'email' => 'vee@kruveewalks.test',
                'accent_color' => '#4C3A8C',
                'promptpay' => '0845566778',
                'hero' => [
                    'image' => 'https://images.unsplash.com/photo-1644027615290-06280cdefd0e?w=1200&q=80',
                    'title' => 'Walking Wat Pho with Kru Vee',
                    'location' => 'Wat Pho · Bangkok',
                    'rating' => '5.0 (94)',
                    'body' => "Licensed cultural guide, history teacher for 20 years. Bangkok's temples through the stories nobody else tells you.",
                ],
                'about' => "Kru means *teacher* in Thai, and that's still how Vee thinks of herself, even on her second career.\n\nFor two decades she taught Thai history at a high school in Thonburi. When she retired she didn't stop; she just changed the classroom. Now her students are travellers from Berlin, Munich, London, Singapore, anyone who would rather hear five stories about a temple than tick off ten.\n\n**Small groups only** (max 6). Every walk includes a tea break at a place tour buses can't park. **Licensed by TAT.**",
                'menu' => [
                    'section_label' => 'WALKS & TOURS',
                    'items' => [
                        ['name' => 'Sunrise Wat Pho & Reclining Buddha', 'price' => '1,200 THB', 'description' => '2 hours · 6:30 am start, before the tour buses arrive.', 'image' => 'https://images.unsplash.com/photo-1704391445538-cf5e763234be?w=800&q=80'],
                        ['name' => 'Grand Palace Half-Day', 'price' => '2,800 THB', 'description' => '4 hours · history of the Chakri dynasty, hidden murals, royal etiquette.', 'image' => 'https://images.unsplash.com/photo-1703508202823-9b3648ca4f18?w=800&q=80'],
                        ['name' => 'Chinatown Stories Walk', 'price' => '1,800 THB', 'description' => '3 hours · gold shops, herbal sellers, immigrant history, tea stop.', 'image' => 'https://images.unsplash.com/photo-1704312970970-58fbe3652eb4?w=800&q=80'],
                        ['name' => 'Custom Cultural Walk', 'price' => 'From 2,000 THB', 'description' => 'Tell me what you\'re curious about, I build the route.', 'image' => 'https://images.unsplash.com/photo-1779246364538-52e314e4a9d9?w=800&q=80'],
                    ],
                ],
                'opening_hours' => [
                    'note' => 'Walks Tuesday to Sunday. Mondays I research and rest.',
                    'hours' => [
                        ['days' => 'Tue–Sun', 'time' => '07:00–18:00'],
                        ['days' => 'Mon', 'time' => 'Closed'],
                    ],
                ],
                'contact_buttons' => [
                    ['channel' => 'whatsapp', 'value' => '+66845566778', 'label' => 'WhatsApp'],
                    ['channel' => 'line', 'value' => 'kruvee.walks', 'label' => 'LINE'],
                    ['channel' => 'facebook', 'value' => 'kruveewalks', 'label' => 'Facebook'],
                ],
                'cta' => [
                    'title' => 'Walk Bangkok with a teacher',
                    'body' => 'Tell me your dates and what curiosities you\'re bringing, I\'ll reply with a route + tea-stop within a day.',
                    'button_label' => 'Book a walk',
                    'button_url' => '#contact-form',
                ],
                'contact_form' => [
                    'title' => 'Message Kru Vee',
                    'intro' => 'Write in any language, we translate both ways. Tell me how many in your group and any history topics you\'re into.',
                ],
            ],

            // ------------------------------------------------------------
            // 5. Sailom Klong Boat Tours, long-tail boats, TH source
            // ------------------------------------------------------------
            [
                'slug' => 'sailom-boats',
                'name' => 'Sailom Klong Boat Tours',
                'locale' => 'th',
                'owner_name' => 'Khun Sailom',
                'email' => 'sailom@sailom-boats.test',
                'accent_color' => '#0F5C5C',
                'promptpay' => '0856677889',
                'hero' => [
                    'image' => 'https://images.unsplash.com/photo-1768757718450-db173fe507ee?w=1200&q=80',
                    'title' => 'Sailom Klong Boat Tours',
                    'location' => 'ท่าเรือปากเกร็ด · กรุงเทพฯ',
                    'rating' => '4.96 (152)',
                    'body' => 'เรือหางยาวลำเดียวในตระกูล, สามรุ่นแล้ว ลุงสายลมพาคุณลัดเลียบคลองธนบุรีและอยุธยาแบบไม่ต้องแชร์กับใคร',
                ],
                'about' => "ปู่ของลุงสายลมขับเรือหางยาวคลองบางหลวงตั้งแต่ปี **พ.ศ. 2502**\n\nวันนี้เป็นรุ่นที่สามแล้ว เรือลำเดียวกัน (เครื่องยนต์เปลี่ยนแล้ว) เส้นทางเดียวกัน เรื่องเล่าเดียวกัน\n\nเรือออกได้ทั้งเส้นคลองบางหลวง คลองมอญ และวัดบางพลัด ลุงเล่าทุกวัดที่ผ่านเป็นภาษาไทย, ถ้าอยากฟังเป็นภาษาอังกฤษ มีลูกชายลุงไปด้วยได้ค่ะ\n\n**เรือ 1 ลำ รับสูงสุด 4 ท่าน** ไม่แชร์กับใคร เป็นทัวร์ส่วนตัวเสมอ",
                'menu' => [
                    'section_label' => 'เส้นทางเรือ',
                    'items' => [
                        ['name' => 'คลองบางหลวง 2 ชั่วโมง', 'price' => '1,500 บาท', 'description' => 'ต่อเรือ, ผ่าน 5 วัดและตลาดน้ำเล็กๆ ที่ไม่อยู่ในแผนที่', 'image' => 'https://images.unsplash.com/photo-1768241154452-c13d3026bfdf?w=800&q=80'],
                        ['name' => 'ตลาดน้ำตลิ่งชัน 4 ชั่วโมง', 'price' => '2,500 บาท', 'description' => 'เช้าตรู่ พายกลับตอนสาย แวะกินข้าวบนเรือ', 'image' => 'https://images.unsplash.com/photo-1590118432058-f2744d6897db?w=800&q=80'],
                        ['name' => 'ทัวร์ถ่ายภาพพระอาทิตย์ขึ้น 3 ชั่วโมง', 'price' => '1,800 บาท', 'description' => 'ออก 5:30 น. หมอกคลองในแสงเช้า ช่างภาพแนะนำ', 'image' => 'https://images.unsplash.com/photo-1576185322547-f2fc77f11836?w=800&q=80'],
                        ['name' => 'เช่าเรือเหมาลำ', 'price' => '800 บาท/ชม.', 'description' => 'บอกเส้นทาง ลุงพา ขั้นต่ำ 2 ชั่วโมง', 'image' => 'https://images.unsplash.com/photo-1743781511190-e579f4bad265?w=800&q=80'],
                    ],
                ],
                'opening_hours' => [
                    'note' => 'เริ่มเรือก่อนพระอาทิตย์ขึ้น สายเกินไป แดดเริ่มแรง',
                    'hours' => [
                        ['days' => 'ทุกวัน', 'time' => '06:00–18:00'],
                        ['days' => 'พระอาทิตย์ขึ้น', 'time' => 'ตามนัด'],
                    ],
                ],
                'contact_buttons' => [
                    ['channel' => 'whatsapp', 'value' => '+66856677889', 'label' => 'WhatsApp'],
                    ['channel' => 'line', 'value' => 'sailom.boats', 'label' => 'LINE'],
                    ['channel' => 'call', 'value' => '+66856677889', 'label' => 'โทร'],
                ],
                'cta' => [
                    'title' => 'จองเรือล่วงหน้า',
                    'body' => 'บอกวันและจำนวนคน ลุงตอบกลับภายในวัน',
                    'button_label' => 'จองเรือ',
                    'button_url' => '#contact-form',
                ],
                'contact_form' => [
                    'title' => 'ส่งข้อความถึงลุงสายลม',
                    'intro' => 'ภาษาอะไรก็ได้, เราแปลให้ทั้งสองทาง บอกวันและจำนวนคนได้เลย',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function createVendor(array $v): void
    {
        $user = User::updateOrCreate(
            ['email' => $v['email']],
            [
                'name' => $v['owner_name'],
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ],
        );

        Vendor::updateOrCreate(
            ['slug' => $v['slug']],
            [
                'user_id' => $user->id,
                'name' => $v['name'],
                'status' => 'live',
                'locale' => $v['locale'],
                'accent_color' => $v['accent_color'] ?? null,
                'onboarding_completed_at' => now(),
                'promptpay_phone' => $v['promptpay'] ?? null,
                'stablecoin_address' => env('DEMO_STABLECOIN_ADDRESS'),
                'stablecoin_chain' => 'ETH',
            ],
        );

        $this->writeStorageFiles($v);
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function writeStorageFiles(array $v): void
    {
        $disk = Storage::disk('vendors');
        $slug = $v['slug'];

        // vendor.yaml
        $disk->put("{$slug}/vendor.yaml", Yaml::dump([
            'slug' => $slug,
            'name' => $v['name'],
            'template' => 'default',
            'locale' => $v['locale'],
            'status' => 'live',
        ], inline: 4, indent: 2));

        // pages/home.yaml
        $disk->put("{$slug}/pages/home.yaml", Yaml::dump([
            'slug' => 'home',
            'components' => [
                ['type' => 'hero', 'file' => '01-hero.md'],
                ['type' => 'about', 'file' => '02-about.md'],
                ['type' => 'menu', 'file' => '03-menu.md'],
                ['type' => 'opening_hours', 'file' => '04-opening-hours.md'],
                ['type' => 'contact_buttons', 'file' => '05-contact-buttons.md'],
                ['type' => 'cta', 'file' => '06-cta.md'],
                ['type' => 'pay_now_trigger', 'file' => '07-pay-now-trigger.md'],
                ['type' => 'contact_form', 'file' => '08-contact-form.md'],
            ],
        ], inline: 4, indent: 2));

        $base = "{$slug}/content/home";

        // 01 hero
        $disk->put("{$base}/01-hero.md", $this->mdFile(
            fields: [
                'title' => $v['hero']['title'],
                'location' => $v['hero']['location'],
                'rating' => $v['hero']['rating'],
                'image' => $v['hero']['image'],
            ],
            body: $v['hero']['body'] ?? '',
        ));

        // 02 about
        $disk->put("{$base}/02-about.md", $this->mdFile(
            fields: ['section_label' => 'ABOUT'],
            body: $v['about'],
        ));

        // 03 menu
        $disk->put("{$base}/03-menu.md", $this->mdFile(
            fields: [
                'section_label' => $v['menu']['section_label'],
                'items' => $v['menu']['items'],
            ],
        ));

        // 04 opening hours
        $disk->put("{$base}/04-opening-hours.md", $this->mdFile(
            fields: [
                'section_label' => 'OPENING HOURS',
                'hours' => $v['opening_hours']['hours'],
                'note' => $v['opening_hours']['note'] ?? null,
            ],
        ));

        // 05 contact buttons
        $disk->put("{$base}/05-contact-buttons.md", $this->mdFile(
            fields: [
                'section_label' => 'CONTACT',
                'buttons' => $v['contact_buttons'],
            ],
        ));

        // 06 cta
        $disk->put("{$base}/06-cta.md", $this->mdFile(
            fields: [
                'title' => $v['cta']['title'],
                'body' => $v['cta']['body'],
                'button_label' => $v['cta']['button_label'],
                'button_url' => $v['cta']['button_url'],
            ],
        ));

        // 07 pay now trigger
        $disk->put("{$base}/07-pay-now-trigger.md", $this->mdFile(
            fields: [
                'label' => 'PAY',
                'title' => "Pay {$v['name']} right here",
                'url' => "/{$slug}/pay",
            ],
        ));

        // 08 contact form
        $disk->put("{$base}/08-contact-form.md", $this->mdFile(
            fields: [
                'section_label' => 'WRITE TO US',
                'title' => $v['contact_form']['title'],
                'intro' => $v['contact_form']['intro'],
                'submit_label' => 'Send',
            ],
        ));
    }

    /**
     * Serialize a Markdown component file: --- frontmatter --- + body.
     *
     * @param  array<string, mixed>  $fields
     */
    private function mdFile(array $fields, string $body = ''): string
    {
        $cleaned = array_filter($fields, fn ($v) => $v !== null && $v !== '');
        $yaml = Yaml::dump($cleaned, inline: 4, indent: 2);

        return "---\n{$yaml}---\n\n{$body}\n";
    }
}
