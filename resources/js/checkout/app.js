const { createApp } = Vue;

createApp({
    data() {
        return {
            currentStep: 1,
            isSubmitting: false,
            cartItems: window.checkoutData?.items || [],
            summary: window.checkoutData?.summary || {
                subtotal: 0,
                shipping_fee: 0,
                discount: 0,
                total: 0,
                free_shipping_threshold: 1000,
                amount_to_free_shipping: 0
            },
            form: {
                shipping_name: window.userName || '',
                shipping_phone: window.userPhone || '',
                shipping_city: '',
                shipping_district: '',
                shipping_address: '',
                payment_method: 'credit_card',
                note: ''
            },
            errors: {},
            // 台灣縣市區域資料
            taiwanData: {
                '台北市': ['中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區'],
                '新北市': ['板橋區', '三重區', '中和區', '永和區', '新莊區', '新店區', '土城區', '蘆洲區', '樹林區', '汐止區', '鶯歌區', '三峽區', '淡水區', '瑞芳區', '五股區', '泰山區', '林口區', '深坑區', '石碇區', '坪林區', '三芝區', '石門區', '八里區', '平溪區', '雙溪區', '貢寮區', '金山區', '萬里區', '烏來區'],
                '桃園市': ['桃園區', '中壢區', '大溪區', '楊梅區', '蘆竹區', '大園區', '龜山區', '八德區', '龍潭區', '平鎮區', '新屋區', '觀音區', '復興區'],
                '台中市': ['中區', '東區', '南區', '西區', '北區', '北屯區', '西屯區', '南屯區', '太平區', '大里區', '霧峰區', '烏日區', '豐原區', '后里區', '石岡區', '東勢區', '和平區', '新社區', '潭子區', '大雅區', '神岡區', '大肚區', '沙鹿區', '龍井區', '梧棲區', '清水區', '大甲區', '外埔區', '大安區'],
                '台南市': ['中西區', '東區', '南區', '北區', '安平區', '安南區', '永康區', '歸仁區', '新化區', '左鎮區', '玉井區', '楠西區', '南化區', '仁德區', '關廟區', '龍崎區', '官田區', '麻豆區', '佳里區', '西港區', '七股區', '將軍區', '學甲區', '北門區', '新營區', '後壁區', '白河區', '東山區', '六甲區', '下營區', '柳營區', '鹽水區', '善化區', '大內區', '山上區', '新市區', '安定區'],
                '高雄市': ['楠梓區', '左營區', '鼓山區', '三民區', '鹽埕區', '前金區', '新興區', '苓雅區', '前鎮區', '旗津區', '小港區', '鳳山區', '林園區', '大寮區', '大樹區', '大社區', '仁武區', '鳥松區', '岡山區', '橋頭區', '燕巢區', '田寮區', '阿蓮區', '路竹區', '湖內區', '茄萣區', '永安區', '彌陀區', '梓官區', '旗山區', '美濃區', '六龜區', '甲仙區', '杉林區', '內門區', '茂林區', '桃源區', '那瑪夏區'],
                '基隆市': ['仁愛區', '信義區', '中正區', '中山區', '安樂區', '暖暖區', '七堵區'],
                '新竹市': ['東區', '北區', '香山區'],
                '新竹縣': ['竹北市', '竹東鎮', '新埔鎮', '關西鎮', '湖口鄉', '新豐鄉', '芎林鄉', '橫山鄉', '北埔鄉', '寶山鄉', '峨眉鄉', '尖石鄉', '五峰鄉'],
                '苗栗縣': ['苗栗市', '頭份市', '竹南鎮', '後龍鎮', '通霄鎮', '苑裡鎮', '卓蘭鎮', '造橋鄉', '西湖鄉', '頭屋鄉', '公館鄉', '銅鑼鄉', '三義鄉', '大湖鄉', '獅潭鄉', '三灣鄉', '南庄鄉', '泰安鄉'],
                '彰化縣': ['彰化市', '員林市', '鹿港鎮', '和美鎮', '北斗鎮', '溪湖鎮', '田中鎮', '二林鎮', '線西鄉', '伸港鄉', '福興鄉', '秀水鄉', '花壇鄉', '芬園鄉', '大村鄉', '埔鹽鄉', '埔心鄉', '永靖鄉', '社頭鄉', '二水鄉', '田尾鄉', '埤頭鄉', '芳苑鄉', '大城鄉', '竹塘鄉', '溪州鄉'],
                '南投縣': ['南投市', '埔里鎮', '草屯鎮', '竹山鎮', '集集鎮', '名間鄉', '鹿谷鄉', '中寮鄉', '魚池鄉', '國姓鄉', '水里鄉', '信義鄉', '仁愛鄉'],
                '雲林縣': ['斗六市', '斗南鎮', '虎尾鎮', '西螺鎮', '土庫鎮', '北港鎮', '莿桐鄉', '林內鄉', '古坑鄉', '大埤鄉', '崙背鄉', '二崙鄉', '麥寮鄉', '台西鄉', '東勢鄉', '褒忠鄉', '四湖鄉', '口湖鄉', '水林鄉', '元長鄉'],
                '嘉義市': ['東區', '西區'],
                '嘉義縣': ['太保市', '朴子市', '布袋鎮', '大林鎮', '民雄鄉', '溪口鄉', '新港鄉', '六腳鄉', '東石鄉', '義竹鄉', '鹿草鄉', '水上鄉', '中埔鄉', '竹崎鄉', '梅山鄉', '番路鄉', '大埔鄉', '阿里山鄉'],
                '屏東縣': ['屏東市', '潮州鎮', '東港鎮', '恆春鎮', '萬丹鄉', '長治鄉', '麟洛鄉', '九如鄉', '里港鄉', '鹽埔鄉', '高樹鄉', '萬巒鄉', '內埔鄉', '竹田鄉', '新埤鄉', '枋寮鄉', '新園鄉', '崁頂鄉', '林邊鄉', '南州鄉', '佳冬鄉', '琉球鄉', '車城鄉', '滿州鄉', '枋山鄉', '三地門鄉', '霧台鄉', '瑪家鄉', '泰武鄉', '來義鄉', '春日鄉', '獅子鄉', '牡丹鄉'],
                '宜蘭縣': ['宜蘭市', '羅東鎮', '蘇澳鎮', '頭城鎮', '礁溪鄉', '壯圍鄉', '員山鄉', '冬山鄉', '五結鄉', '三星鄉', '大同鄉', '南澳鄉'],
                '花蓮縣': ['花蓮市', '鳳林鎮', '玉里鎮', '新城鄉', '吉安鄉', '壽豐鄉', '光復鄉', '豐濱鄉', '瑞穗鄉', '富里鄉', '秀林鄉', '萬榮鄉', '卓溪鄉'],
                '台東縣': ['台東市', '成功鎮', '關山鎮', '卑南鄉', '鹿野鄉', '池上鄉', '東河鄉', '長濱鄉', '太麻里鄉', '大武鄉', '綠島鄉', '蘭嶼鄉', '延平鄉', '海端鄉', '達仁鄉', '金峰鄉'],
                '澎湖縣': ['馬公市', '湖西鄉', '白沙鄉', '西嶼鄉', '望安鄉', '七美鄉'],
                '金門縣': ['金城鎮', '金湖鎮', '金沙鎮', '金寧鄉', '烈嶼鄉', '烏坵鄉'],
                '連江縣': ['南竿鄉', '北竿鄉', '莒光鄉', '東引鄉']
            }
        };
    },
    computed: {
        cities() {
            return Object.keys(this.taiwanData);
        },
        currentDistricts() {
            return this.taiwanData[this.form.shipping_city] || [];
        },
        totalAmount() {
            let total = this.summary.subtotal + this.summary.shipping_fee - this.summary.discount;
            // 貨到付款加收 30 元手續費
            if (this.form.payment_method === 'cash_on_delivery') {
                total += 30;
            }
            return total;
        }
    },
    methods: {
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        onCityChange() {
            this.form.shipping_district = '';
        },
        validateForm() {
            this.errors = {};

            if (!this.form.shipping_name.trim()) {
                this.errors.shipping_name = '請輸入收件人姓名';
            }

            if (!this.form.shipping_phone.trim()) {
                this.errors.shipping_phone = '請輸入聯絡電話';
            } else if (!/^09\d{8}$/.test(this.form.shipping_phone.replace(/\s/g, ''))) {
                this.errors.shipping_phone = '請輸入有效的手機號碼';
            }

            if (!this.form.shipping_city) {
                this.errors.shipping_city = '請選擇城市';
            }

            if (!this.form.shipping_district) {
                this.errors.shipping_district = '請選擇區域';
            }

            if (!this.form.shipping_address.trim()) {
                this.errors.shipping_address = '請輸入詳細地址';
            }

            if (!this.form.payment_method) {
                this.errors.payment_method = '請選擇付款方式';
            }

            return Object.keys(this.errors).length === 0;
        },
        async submitOrder() {
            if (!this.validateForm()) {
                return;
            }

            this.isSubmitting = true;

            try {
                const response = await fetch('/api/checkout/order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    // 訂單建立成功，跳轉到成功頁面
                    window.location.href = result.data.redirect_url;
                } else {
                    alert(result.message || '訂單建立失敗，請稍後再試');
                }
            } catch (error) {
                console.error('Order submission error:', error);
                alert('網路錯誤，請稍後再試');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}).mount('#app');
