<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 取得標籤 ID 對應
        $tagIds = DB::table('tags')->pluck('id', 'name')->toArray();

        $products = [
            // 傢俱類商品 (category_id: 1)
            [
                'category_id' => 1, 'brand_id' => 1,
                'name' => '丹麥極簡曲木躺椅',
                'description' => '丹麥設計，極簡風格的曲木躺椅，舒適且具有藝術感。',
                'price' => 2850.00, 'original_price' => 2850.00,
                'image' => 'DenmarkBentWoodChair.jpg',
                'tag' => '新品', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 4,
                'name' => '幾何造型邊桌',
                'description' => '獨特的幾何造型設計，功能與美感兼具的邊桌。',
                'price' => 890.00, 'original_price' => 890.00,
                'image' => 'GeometricSideTable.jpg',
                'tag' => '熱銷', 'stock' => 25, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 2,
                'name' => '簡約橡木書桌',
                'description' => '採用天然橡木製作，簡約實用的北歐風格書桌。',
                'price' => 1890.00, 'original_price' => 2290.00,
                'image' => 'SimpleDesk.jpg',
                'tag' => '特價', 'stock' => 12, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 3,
                'name' => '實木餐椅',
                'description' => '符合人體工學的實木餐椅，原木色展現自然質感。',
                'price' => 650.00, 'original_price' => 650.00,
                'image' => 'DiningChair.jpg',
                'tag' => null, 'stock' => 40, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 1,
                'name' => '北歐風雙人沙發',
                'description' => '柔軟舒適的雙人沙發，採用高品質面料與填充物。',
                'price' => 3580.00, 'original_price' => 3580.00,
                'image' => 'NordicLoveSeat.jpg',
                'tag' => '新品', 'stock' => 8, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 4,
                'name' => '多功能儲物櫃',
                'description' => '模組化設計，可自由組合的多功能儲物系統。',
                'price' => 1250.00, 'original_price' => 1580.00,
                'image' => 'Locker.jpg',
                'tag' => '特價', 'stock' => 20, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 2,
                'name' => '極簡電視櫃',
                'description' => '簡約設計的電視櫃，白橡木材質耐用美觀。',
                'price' => 1690.00, 'original_price' => 1690.00,
                'image' => 'SimpleTVcabinet.jpg',
                'tag' => '熱銷', 'stock' => 18, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 3,
                'name' => '圓形咖啡桌',
                'description' => '經典圓形設計搭配天然大理石桌面，奢華質感。',
                'price' => 2280.00, 'original_price' => 2280.00,
                'image' => 'RoundCoffeTable.jpg',
                'tag' => null, 'stock' => 10, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 4,
                'name' => '懶人單椅',
                'description' => '舒適的懶人單椅，高密度泡棉填充，灰色布面。',
                'price' => 980.00, 'original_price' => 1280.00,
                'image' => 'LazyChair.jpg',
                'tag' => '特價', 'stock' => 22, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 1,
                'name' => '兒童成長書桌椅組',
                'description' => '可調整高度的兒童書桌椅組，陪伴孩子成長。',
                'price' => 1580.00, 'original_price' => 1580.00,
                'image' => 'childrenTableChair.jpg',
                'tag' => '新品', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 6,
                'name' => '藤編休閒椅',
                'description' => '天然藤編材質，透氣舒適的休閒椅。',
                'price' => 750.00, 'original_price' => 750.00,
                'image' => 'rattanChair.jpg',
                'tag' => null, 'stock' => 30, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 2,
                'name' => '手工陶瓷花器',
                'description' => '純手工製作的陶瓷花器，米白色調溫潤自然。',
                'price' => 240.00, 'original_price' => 320.00,
                'image' => 'HandmadeCeramicFlowerVase.jpg',
                'tag' => '特價', 'stock' => 35, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 5,
                'name' => '金屬置物架',
                'description' => '工業風金屬置物架，堅固耐用的黑色烤漆。',
                'price' => 580.00, 'original_price' => 580.00,
                'image' => 'MetalShelves.jpg',
                'tag' => '熱銷', 'stock' => 28, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 7,
                'name' => '經典設計椅 - Panton Chair',
                'description' => 'Vitra經典設計椅，一體成型的流線造型。',
                'price' => 3280.00, 'original_price' => 3280.00,
                'image' => 'PantonChair.jpg',
                'tag' => '新品', 'stock' => 12, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 8,
                'name' => '天鵝椅 - 經典復刻',
                'description' => 'Fritz Hansen經典天鵝椅復刻版，優雅曲線設計。',
                'price' => 4580.00, 'original_price' => 5280.00,
                'image' => 'SwanChair.jpg',
                'tag' => '特價', 'stock' => 6, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 10,
                'name' => '模組化層板系統',
                'description' => 'String經典模組化層板系統，可自由組合搭配。',
                'price' => 1890.00, 'original_price' => 1890.00,
                'image' => 'ModularShelvingSystem.jpg',
                'tag' => '熱銷', 'stock' => 20, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],

            // 燈飾類商品 (category_id: 2)
            [
                'category_id' => 2, 'brand_id' => 3,
                'name' => '北歐風格吊燈',
                'description' => '簡約北歐風格吊燈，霧灰色調營造柔和氛圍。',
                'price' => 1280.00, 'original_price' => 1280.00,
                'image' => 'NordicChandelier.jpg',
                'tag' => null, 'stock' => 20, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 5,
                'name' => '極簡立燈',
                'description' => '優雅的金色立燈，可調整角度，適合閱讀使用。',
                'price' => 850.00, 'original_price' => 1080.00,
                'image' => 'SimpleFloorLamp.jpg',
                'tag' => '特價', 'stock' => 25, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 2,
                'name' => '玻璃球型吊燈',
                'description' => '現代感十足的玻璃球型吊燈，透明材質通透明亮。',
                'price' => 1580.00, 'original_price' => 1580.00,
                'image' => 'GlassSphericalChandelier.jpg',
                'tag' => '新品', 'stock' => 12, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 4,
                'name' => '工業風吊燈組',
                'description' => '復古工業風三燈吊燈，適合餐廳或吧台使用。',
                'price' => 1890.00, 'original_price' => 1890.00,
                'image' => 'IndustrialPendantLights.jpg',
                'tag' => '熱銷', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 1,
                'name' => '木質檯燈',
                'description' => '溫潤的木質檯燈，自然色調帶來溫馨感受。',
                'price' => 450.00, 'original_price' => 580.00,
                'image' => 'WoodenLamp.jpg',
                'tag' => '特價', 'stock' => 30, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 3,
                'name' => '月球造型夜燈',
                'description' => '3D列印月球造型夜燈，浪漫氛圍照明。',
                'price' => 2800.00, 'original_price' => 2800.00,
                'image' => 'MoonNightLight.jpg',
                'tag' => null, 'stock' => 45, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 6,
                'name' => 'LED智能燈泡',
                'description' => '智能WiFi燈泡，可透過手機App調整色溫與亮度。',
                'price' => 1200.00, 'original_price' => 1200.00,
                'image' => 'LEDsmartLight.jpg',
                'tag' => '新品', 'stock' => 100, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 5,
                'name' => '壁燈',
                'description' => '簡約設計壁燈，節省空間且提供充足照明。',
                'price' => 3600.00, 'original_price' => 4800.00,
                'image' => 'wallLamp.jpg',
                'tag' => '特價', 'stock' => 35, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 2,
                'name' => '藤編吊燈',
                'description' => '手工編織藤編吊燈，自然風格與現代設計的結合。',
                'price' => 980.00, 'original_price' => 980.00,
                'image' => 'RattanChandelier.jpg',
                'tag' => '熱銷', 'stock' => 18, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 4,
                'name' => '折疊式閱讀燈',
                'description' => '可折疊攜帶的USB充電閱讀燈，適合旅行使用。',
                'price' => 1500.00, 'original_price' => 1500.00,
                'image' => 'readingLight.jpg',
                'tag' => null, 'stock' => 60, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 9,
                'name' => 'PH5 吊燈 - 經典款',
                'description' => 'Louis Poulsen經典PH5吊燈，完美的光線分佈設計。',
                'price' => 3890.00, 'original_price' => 3890.00,
                'image' => 'PH5PendantLight.jpg',
                'tag' => '新品', 'stock' => 8, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 9,
                'name' => 'Panthella 桌燈',
                'description' => 'Louis Poulsen經典桌燈，半球形燈罩散發柔和光線。',
                'price' => 1850.00, 'original_price' => 2280.00,
                'image' => 'PanthellaTableLamp.jpg',
                'tag' => '特價', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],

            // 織品類商品 (category_id: 3)
            [
                'category_id' => 3, 'brand_id' => 6,
                'name' => '手織棉麻抱枕套',
                'description' => '手工編織棉麻材質，親膚舒適的抱枕套。',
                'price' => 1800.00, 'original_price' => 1800.00,
                'image' => 'HandmadePillowcase.jpg',
                'tag' => null, 'stock' => 50, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 2,
                'name' => '純棉四件式床組',
                'description' => '100%純棉四件式床組，簡約北歐風格圖案。',
                'price' => 580.00, 'original_price' => 720.00,
                'image' => 'PureCottonBedSheet.jpg',
                'tag' => '特價', 'stock' => 25, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 6,
                'name' => '羊毛地毯',
                'description' => '手工製作羊毛地毯，幾何圖案設計時尚大方。',
                'price' => 1280.00, 'original_price' => 1280.00,
                'image' => 'woolCarpet.jpg',
                'tag' => '新品', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 2,
                'name' => '亞麻窗簾',
                'description' => '天然亞麻材質窗簾，米白色調柔和自然。',
                'price' => 450.00, 'original_price' => 450.00,
                'image' => 'linenCurtains.jpg',
                'tag' => '熱銷', 'stock' => 30, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 6,
                'name' => '針織毛毯',
                'description' => '柔軟舒適的針織毛毯，灰色調百搭實用。',
                'price' => 320.00, 'original_price' => 420.00,
                'image' => 'knittedBlanket.jpg',
                'tag' => '特價', 'stock' => 40, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 2,
                'name' => '刺繡桌巾',
                'description' => '精緻刺繡桌巾，北歐風格圖案優雅大方。',
                'price' => 2800.00, 'original_price' => 2800.00,
                'image' => 'EmbroideredTablecloth.jpg',
                'tag' => null, 'stock' => 35, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 6,
                'name' => '浴巾套組',
                'description' => '高品質純棉浴巾套組，吸水性佳且柔軟舒適。',
                'price' => 220.00, 'original_price' => 220.00,
                'image' => 'bathTowel.jpg',
                'tag' => '新品', 'stock' => 45, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 2,
                'name' => '法蘭絨床包組',
                'description' => '溫暖的法蘭絨床包組，冬季保暖必備。',
                'price' => 480.00, 'original_price' => 620.00,
                'image' => 'FlannelBedPack.jpg',
                'tag' => '特價', 'stock' => 28, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 6,
                'name' => '棉麻沙發套',
                'description' => '實用的棉麻沙發套，可拆洗設計方便清潔。',
                'price' => 360.00, 'original_price' => 360.00,
                'image' => 'CottonSofa.jpg',
                'tag' => '熱銷', 'stock' => 22, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 2,
                'name' => '編織收納籃',
                'description' => '天然材質編織收納籃，三種尺寸可堆疊收納。',
                'price' => 240.00, 'original_price' => 240.00,
                'image' => 'storageBasket.jpg',
                'tag' => null, 'stock' => 38, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 8,
                'name' => '經典羊毛毯',
                'description' => 'Fritz Hansen設計師款羊毛毯，經典北歐紋樣。',
                'price' => 890.00, 'original_price' => 890.00,
                'image' => 'woolBlanket.jpg',
                'tag' => '新品', 'stock' => 20, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],

            // 餐廚類商品 (category_id: 4)
            [
                'category_id' => 4, 'brand_id' => 5,
                'name' => '黃銅燭台組',
                'description' => '精緻黃銅材質燭台組，三件式設計可自由搭配。',
                'price' => 360.00, 'original_price' => 450.00,
                'image' => 'BrassCandlestick.jpg',
                'tag' => '特價', 'stock' => 30, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 3,
                'name' => '陶瓷餐具組',
                'description' => '簡約設計陶瓷餐具組，六人份完整配置。',
                'price' => 890.00, 'original_price' => 890.00,
                'image' => 'ceramicTableware.jpg',
                'tag' => '熱銷', 'stock' => 20, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 5,
                'name' => '不鏽鋼鍋具組',
                'description' => '高品質不鏽鋼鍋具組，適用各種爐具。',
                'price' => 1580.00, 'original_price' => 1980.00,
                'image' => 'stainlessSteelPots.jpg',
                'tag' => '特價', 'stock' => 15, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 4,
                'name' => '琺瑯水壺',
                'description' => '經典琺瑯水壺，復古造型與現代功能的結合。',
                'price' => 2400.00, 'original_price' => 2400.00,
                'image' => 'kettle.jpg',
                'tag' => '新品', 'stock' => 40, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 5,
                'name' => '木質餐具收納盒',
                'description' => '實用的木質餐具收納盒，分隔設計井然有序。',
                'price' => 1800.00, 'original_price' => 1800.00,
                'image' => 'CutleryStorageBox.jpg',
                'tag' => null, 'stock' => 50, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 3,
                'name' => '手沖咖啡壺組',
                'description' => '優雅的玻璃手沖咖啡壺組，享受精品咖啡時光。',
                'price' => 420.00, 'original_price' => 550.00,
                'image' => 'coffeePot.jpg',
                'tag' => '特價', 'stock' => 25, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 5,
                'name' => '北歐風砧板',
                'description' => '天然橡木製砧板，耐用且具質感。',
                'price' => 1500.00, 'original_price' => 1500.00,
                'image' => 'choppingBoard.jpg',
                'tag' => '熱銷', 'stock' => 60, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 4,
                'name' => '玻璃密封罐組',
                'description' => '透明玻璃密封罐組，食材收納一目了然。',
                'price' => 2200.00, 'original_price' => 2200.00,
                'image' => 'sealedJar.jpg',
                'tag' => null, 'stock' => 45, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 3,
                'name' => '陶瓷調味罐組',
                'description' => '簡約設計陶瓷調味罐組，配有竹製蓋子。',
                'price' => 1600.00, 'original_price' => 2100.00,
                'image' => 'seasoningJar.jpg',
                'tag' => '特價', 'stock' => 55, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 5,
                'name' => '不鏽鋼刀具組',
                'description' => '專業級不鏽鋼刀具組，鋒利耐用且易於保養。',
                'price' => 9800.00, 'original_price' => 9800.00,
                'image' => 'Knives.jpg',
                'tag' => '新品', 'stock' => 18, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 4, 'brand_id' => 7,
                'name' => '義式摩卡壺',
                'description' => 'Vitra設計摩卡壺，義式咖啡的完美演繹。',
                'price' => 3200.00, 'original_price' => 3200.00,
                'image' => 'mokaPot.jpg',
                'tag' => '熱銷', 'stock' => 35, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 1, 'brand_id' => 9,
                'name' => 'AJ落地燈',
                'description' => 'Louis Poulsen設計的AJ落地燈，經典丹麥設計。',
                'price' => 4280.00, 'original_price' => 4280.00,
                'image' => 'floorLamp.jpg',
                'tag' => '新品', 'stock' => 10, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 2, 'brand_id' => 10,
                'name' => 'String照明組 - LED款',
                'description' => 'String系統專用LED照明組，完美融入層板系統。',
                'price' => 680.00, 'original_price' => 820.00,
                'image' => 'StringLighting.jpg',
                'tag' => '特價', 'stock' => 25, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'category_id' => 3, 'brand_id' => 1,
                'name' => 'Normann抱枕',
                'description' => 'Normann Copenhagen經典幾何圖案抱枕，北歐美學代表作。',
                'price' => 2600.00, 'original_price' => 2600.00,
                'image' => 'NormannPillow.jpg',
                'tag' => '熱銷', 'stock' => 50, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
        ];

        // 準備商品資料與標籤關聯
        $productTagRelations = [];

        foreach ($products as $product) {
            $tagName = $product['tag'];
            unset($product['tag']); // 移除 tag 欄位

            // 插入商品並取得 ID
            $productId = DB::table('products')->insertGetId($product);

            // 如果有標籤，建立關聯
            if ($tagName && isset($tagIds[$tagName])) {
                $productTagRelations[] = [
                    'product_id' => $productId,
                    'tag_id' => $tagIds[$tagName],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // 批次插入商品與標籤的關聯
        if (!empty($productTagRelations)) {
            DB::table('product_tag')->insert($productTagRelations);
        }
    }
}
