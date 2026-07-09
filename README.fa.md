# ماژول Call Now Button برای جوملا

[English](README.md) | **فارسی**

[![Joomla](https://img.shields.io/badge/Joomla-5.0%20%7C%206.0-blue.svg)](https://www.joomla.org)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4%20%7C%208.5-777BB4.svg)](https://www.php.net/)

ماژول حرفه‌ای دکمه تماس شناور برای جوملا. راه‌حلی مدرن و واکنش‌گرا برای نمایش دکمه‌های تماس (تلفن، واتساپ، لینک سفارشی) با گزینه‌های گسترده شخصی‌سازی.

![پیش‌نمایش Call Now Button](https://raw.githubusercontent.com/mostafaafrouzi/joomla-call-now-button/main/Joomla%20Call%20Now%20Bottom.png)

## ✨ ویژگی‌های کلیدی

### انواع دکمه
- **دکمه تکی**: تماس/ارتباط با یک کلیک
- **Multibutton (بازشونده)**: چند دکمه عمل در یک منوی شناور

### انواع لینک
- **تلفن**: لینک مستقیم `tel:`
- **واتساپ**: لینک چت با پشتیبانی کد کشور
- **URL سفارشی**: هر لینک با کنترل `rel` و `target`

### ظاهر
- **آیکون دایره‌ای**: دکمه شناور کلاسیک فقط با آیکون
- **آیکون + متن**: دکمه pill با آیکون و برچسب متنی

### موقعیت و نمایش
- **۸ موقعیت**: پایین/بالا (چپ، وسط، راست)، وسط (چپ، راست)
- **تمام‌عرض**: نمایش full-width در بالا یا پایین (فقط حالت Icon with Text)
- **حاشیه سفارشی**: کنترل فاصله از لبه صفحه
- **سایز واکنش‌گرا**: کنترل سایز برای دسکتاپ، تبلت و موبایل

### شخصی‌سازی
- **۱۸ آیکون داخلی**: تلفن، واتساپ، تلگرام، اینستاگرام، فیسبوک، توییتر، لینکدین، یوتیوب و بیشتر
- **آپلود آیکون سفارشی**
- **CSS سفارشی**: قوانین CSS فرانت‌اند با فیلتر امنیتی
- **بازنویسی رنگ CSS**: پشتیبانی از `var(--theme-color)` برای رنگ دکمه و آیکون
- **تایپوگرافی**: اندازه، رنگ و وزن فونت برای دکمه‌های متنی
- **انیمیشن**: Pulse، Bounce، Shake
- **Z-Index**: کنترل لایه نمایش

### Multibutton
- **تم Tooltip**: پس‌زمینه روشن/تیره
- **نمایش عنوان**: در hover، همیشه، یا هرگز
- **استایل هر آیتم**: رنگ و آیکون جداگانه
- **انیمیشن**: باز شدن پلکانی منو

### SEO و استانداردها
- **بهینه SEO**: `rel` مناسب (nofollow، noopener، noreferrer)
- **دسترسی**: ARIA labels و alt text
- **استاندارد جوملا**: سازگار با Joomla 5.x و 6.x
- **Namespace**: پیاده‌سازی صحیح namespace جوملا

### ویژگی‌های فنی
- **طراحی واکنش‌گرا**: mobile-first
- **حالت نمایش**: media query امن برای cache/CDN (breakpoint 768px از v1.1.0)
- **Scope هر instance**: شناسه یکتا `#cnb-mod-{moduleId}` برای چند instance
- **چندزبانه**: انگلیسی و فارسی
- **به‌روزرسانی خودکار**: از طریق GitHub Releases
- **Changelog**: نمایش تغییرات در ادمین جوملا

## 🆕 جدید در v1.1.0

جزئیات کامل در [یادداشت انتشار v1.1.0](https://github.com/mostafaafrouzi/joomla-call-now-button/releases/tag/v1.1.0). خلاصه:

- **رفع باگ**: display_mode در multibutton، انتخاب آیکون در ردیف جدید subform، ID تکراری، FOUC سایز دکمه، کلیک label در multibutton، بارگذاری `IconRepository` در فرانت‌اند
- **جدید**: فیلد CSS سفارشی، بازنویسی رنگ CSS، assetهای icon selector، کلاس `IconRepository`
- **بهبود**: پشتیبانی کیبورد multibutton، scope CSS هر instance، CI lint برای PHP 8.1 تا 8.5

## 📦 نصب

### روش ۱: دانلود از GitHub

1. به [Releases](https://github.com/mostafaafrouzi/joomla-call-now-button/releases) بروید
2. آخرین نسخه را دانلود کنید
3. در ادمین جوملا: **Extensions > Manage > Install**
4. فایل ZIP را آپلود کنید

### روش ۲: نصب مستقیم

```
https://github.com/mostafaafrouzi/joomla-call-now-button/releases/latest/download/mod_callnowbutton.zip
```

**نکته:** این لینک همیشه آخرین نسخه را دانلود می‌کند (مثلاً `mod_callnowbutton-1.1.0.zip`).

## 🔄 به‌روزرسانی خودکار

1. در ادمین: **System > Update > Extensions**
2. **Check for Updates**
3. **Update**

### مشاهده Changelog

- **Manage Extensions**: کلیک روی شماره نسخه
- **Update Extensions**: دکمه Changelog

## 📋 پیش‌نیازها

- **جوملا**: 5.0.0 یا بالاتر (سازگار با Joomla 6.x)
- **PHP**: 8.1.0 یا بالاتر (8.1 تا 8.5)
- **مرورگر**: مرورگرهای مدرن با CSS3 و JavaScript

## 🔧 عیب‌یابی

### جابه‌جایی دکمه یا آیکون

در تب **Advanced** ماژول، **Module Style** را روی **None** بگذارید (برخی قالب‌ها padding اضافه می‌کنند).

### CSS برای instance خاص

هر instance شناسه `#cnb-mod-{moduleId}` دارد. CSS تولیدشده خودکار scope می‌شود؛ **CSS سفارشی** scope نمی‌شود — خودتان شناسه را در selector بیاورید.

```css
#cnb-mod-42 .cnb-button {
    bottom: 30px !important;
}
```

### فیلد CSS سفارشی

- فقط در فرانت‌اند اعمال می‌شود
- کاراکترهای `<` و `>` حذف می‌شوند (امنیت)
- `@import`، `javascript:` و الگوهای مشابه مسدودند

### حالت نمایش (فقط موبایل / فقط دسکتاپ)

از v1.1.0 با **media query CSS** روی wrapper کار می‌کند — سازگار با page cache و CDN. breakpoint: **768px**.

### ارتقا از v1.0.0

- اگر در CSS از `#callnowbutton` استفاده کرده‌اید، به `#cnb-mod-{moduleId}` تغییر دهید
- `display_mode` در multibutton اکنون طبق تنظیمات اعمال می‌شود

## 👨‍💻 توسعه‌دهنده

**مصطفی افروزی**  
*طراح و توسعه‌دهنده وب، متخصص SEO و بازاریابی دیجیتال*

- 🌐 **وب‌سایت**: [afrouzi.ir](https://afrouzi.ir?utm_source=github&utm_medium=readme_fa&utm_campaign=call_now_button)

## 📄 مجوز

GNU General Public License نسخه 2 یا بالاتر.

## 🙏 حمایت

- ⭐ Star به مخزن
- 🐛 گزارش باگ
- 💡 پیشنهاد بهبود
- 🔄 معرفی به دیگران

---

**با ❤️ برای جامعه جوملا**
