# Call Now Button Module for Joomla

یک ماژول حرفه‌ای جوملا برای افزودن دکمه تماس فوری به وب‌سایت شما. این ماژول نسخه جوملایی افزونه محبوب "Call Now Button" وردپرس است.

## ویژگی‌ها

- ✅ سازگار با Joomla 5 و 6
- ✅ طراحی ریسپانسیو و مدرن
- ✅ پشتیبانی از موبایل و دسکتاپ
- ✅ قابل تنظیم رنگ، موقعیت و اندازه
- ✅ ردیابی کلیک در Google Analytics
- ✅ ردیابی تبدیل در Google Ads
- ✅ کنترل نمایش در صفحات مختلف
- ✅ پشتیبانی چندزبانه (انگلیسی و فارسی)

## نصب

1. فایل ZIP ماژول را دانلود کنید
2. در پنل مدیریت جوملا به **Extensions > Manage > Install** بروید
3. فایل ZIP را آپلود و نصب کنید
4. به **Extensions > Modules** بروید
5. ماژول "Call Now Button" را پیدا کرده و فعال کنید
6. تنظیمات را پیکربندی کنید:
   - شماره تلفن خود را وارد کنید
   - رنگ و موقعیت دکمه را انتخاب کنید
   - تنظیمات نمایش را پیکربندی کنید

## تنظیمات

### تنظیمات پایه
- **فعال‌سازی دکمه**: فعال یا غیرفعال کردن دکمه
- **شماره تلفن**: شماره تلفن با کد کشور (مثال: +989123456789)
- **متن دکمه**: متن اختیاری برای نمایش روی دکمه

### تنظیمات نمایش
- **ظاهر دکمه**: دکمه دایره‌ای، عرض کامل پایین، عرض کامل بالا
- **موقعیت**: 8 موقعیت مختلف (پایین چپ/وسط/راست، بالا چپ/وسط/راست، وسط چپ/راست)
- **رنگ دکمه**: رنگ پس‌زمینه دکمه
- **رنگ آیکون**: رنگ آیکون تلفن
- **اندازه دکمه**: 70% تا 130%
- **Z-Index**: کنترل ترتیب قرارگیری

### تنظیمات نمایش
- **حالت نمایش**: همه دستگاه‌ها، فقط موبایل، فقط دسکتاپ
- **مخفی در صفحه اصلی**: مخفی کردن دکمه در صفحه اصلی
- **محدودیت صفحات**: نمایش فقط در صفحات خاص یا مخفی در صفحات خاص

### تنظیمات ردیابی
- **ردیابی کلیک**: ردیابی کلیک‌ها در Google Analytics
- **نوع ردیابی**: Google Analytics 4، Universal Analytics، Classic Analytics
- **ردیابی تبدیل**: ردیابی به عنوان تبدیل در Google Ads

## ساختار فایل‌ها

```
mod_callnowbutton/
├── mod_callnowbutton.xml          # فایل manifest
├── mod_callnowbutton.php          # فایل اصلی ماژول
├── helper.php                     # Helper کلاس
├── services/
│   └── provider.php               # Service Provider
├── src/
│   ├── Site/
│   │   ├── Dispatcher/
│   │   │   └── Dispatcher.php    # Module Dispatcher
│   │   └── Helper/
│   │       └── CallNowButtonHelper.php  # Helper کلاس
│   └── Extension/
│       └── CallNowButtonModule.php
├── tmpl/
│   └── default.php                # قالب پیش‌فرض
├── media/
│   └── css/
│       └── call-now-button.css    # استایل‌های CSS
└── language/
    ├── en-GB/                     # فایل‌های زبان انگلیسی
    └── fa-IR/                     # فایل‌های زبان فارسی
```

## استفاده

پس از نصب و فعال‌سازی ماژول، دکمه به صورت خودکار در صفحات وب‌سایت شما نمایش داده می‌شود (بر اساس تنظیمات نمایش).

برای استفاده در کد PHP:

```php
use Joomla\Module\CallNowButton\Site\Helper\CallNowButtonHelper;
use Joomla\Registry\Registry;

$params = new Registry([
    'active' => 1,
    'phone_number' => '+989123456789',
    'button_color' => '#25D366',
    'position' => 'bottom-right'
]);

$helper = new CallNowButtonHelper($params);
if ($helper->shouldRender()) {
    echo $helper->renderButton();
}
```

## پشتیبانی

برای پشتیبانی و گزارش باگ، لطفاً به [GitHub Issues](https://github.com/your-repo/issues) مراجعه کنید.

## مجوز

این ماژول تحت مجوز GNU General Public License version 2 or later منتشر شده است.

## تغییرات

### نسخه 1.0.0
- انتشار اولیه
- سازگاری با Joomla 5 و 6
- تمام ویژگی‌های پایه

## اعتبار

این ماژول بر اساس افزونه وردپرس "Call Now Button" ساخته شده است که توسط [NowButtons.com](https://nowbuttons.com) توسعه یافته است.

