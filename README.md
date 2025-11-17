# Call Now Button Module for Joomla

[![Joomla](https://img.shields.io/badge/Joomla-5.0%20%7C%206.0-blue.svg)](https://www.joomla.org)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](https://www.php.net/)

یک ماژول حرفه‌ای و پیشرفته برای افزودن دکمه تماس شناور به سایت Joomla شما. این ماژول راه‌حلی مدرن و ریسپانسیو برای نمایش دکمه‌های تماس (تلفن، واتساپ، لینک سفارشی) با امکانات سفارشی‌سازی گسترده ارائه می‌دهد.

## ✨ ویژگی‌های کلیدی

- 🎯 **دو نوع دکمه**: تک دکمه یا چند دکمه (Expanding)
- 📱 **انواع لینک**: تلفن، واتساپ، URL سفارشی
- 🎨 **دو استایل**: دکمه دایره‌ای یا آیکون با متن (pill-shaped)
- 📍 **8 موقعیت** + حالت تمام عرض
- 📱 **Responsive** با کنترل سایز برای هر دستگاه
- ✨ **انیمیشن**: Pulse، Bounce، Shake
- 🎨 **18 آیکون داخلی** + امکان آپلود آیکون سفارشی
- 🌐 **چندزبانه**: انگلیسی و فارسی
- ♿ **دسترسی‌پذیر**: ARIA labels و alt text
- 🔍 **بهینه SEO**: rel، target، title attributes

## 📦 نصب

### روش 1: دانلود از GitHub

1. به [Releases](https://github.com/mostafaafrouzi/joomla-call-now-button/releases) بروید
2. آخرین نسخه را دانلود کنید
3. در Joomla Admin: **Extensions > Manage > Install**
4. فایل ZIP را آپلود کنید

### روش 2: نصب مستقیم

از لینک زیر نصب کنید:
```
https://github.com/mostafaafrouzi/joomla-call-now-button/releases/latest/download/mod_callnowbutton-latest.zip
```

## 🔄 بروزرسانی خودکار

ماژول از سیستم بروزرسانی خودکار Joomla پشتیبانی می‌کند:

1. در Joomla Admin: **System > Update > Extensions**
2. روی **Check for Updates** کلیک کنید
3. بروزرسانی‌های موجود نمایش داده می‌شوند
4. روی **Update** کلیک کنید

### نمایش Changelog

- **در Manage Extensions**: روی شماره نسخه کلیک کنید
- **در Update Extensions**: دکمه Changelog برای مشاهده تغییرات

## 📖 مستندات کامل

مستندات کامل در فایل [mod_callnowbutton/README.md](mod_callnowbutton/README.md) موجود است.

## 🛠️ توسعه

### ساختار Repository

```
joomla-call-now-button/
├── mod_callnowbutton/      # کد ماژول
├── updates/                # فایل‌های بروزرسانی
│   ├── updates.xml
│   └── changelog.xml
├── build/                  # اسکریپت‌های build
└── .github/workflows/      # GitHub Actions (خودکار)
```

### Release خودکار با GitHub Actions

```bash
# 1. به‌روزرسانی نسخه در mod_callnowbutton.xml
# 2. به‌روزرسانی changelog.xml
# 3. Commit و push

git add .
git commit -m "Release v1.0.1"
git push origin main

# 4. ایجاد tag
git tag v1.0.1
git push --tags

# 5. GitHub Actions به صورت خودکار:
#    - ZIP می‌سازد
#    - Release ایجاد می‌کند
#    - updates.xml را به‌روزرسانی می‌کند
```

### Build محلی (اختیاری)

```batch
build\build.bat        # Windows
build/build.sh         # Linux/Mac
```

## 👨‍💻 توسعه‌دهنده

**Mostafa Afrouzi**  
*Web Designer & Developer, SEO & Digital Marketing Specialist*

- 🌐 **Website**: [afrouzi.ir](https://afrouzi.ir)
- 📧 **Email**: [mostafa.afrouzi@gmail.com](mailto:mostafa.afrouzi@gmail.com)
- 📱 **Phone**: [+98 917 626 2858](tel:+989176262858)
- 💼 **LinkedIn**: [linkedin.com/in/mostafaafrouzi](https://www.linkedin.com/in/mostafaafrouzi/)
- 🐙 **GitHub**: [github.com/mostafaafrouzi](https://github.com/mostafaafrouzi)
- 💬 **WhatsApp**: [Send Message](https://wa.me/989176262858)
- 📮 **Telegram**: [@mostafaafrouzi](https://t.me/mostafaafrouzi)

### خدمات حرفه‌ای

- طراحی و توسعه وب/فروشگاه (Joomla، WordPress، Laravel، Vue.js)
- تولید افزونه، ماژول، پلاگین و کامپوننت سفارشی Joomla
- طراحی قالب اختصاصی Joomla
- مهاجرت جوملا از نسخه قبلی به آخرین نسخه
- سئو تکنیکال و بهینه‌سازی موتورهای جستجو
- تبلیغات گوگل و PPC
- اتوماسیون بازاریابی
- توسعه اپلیکیشن موبایل

**مشاوره رایگان** — برای دریافت قیمت دقیق تماس بگیرید.

## 📄 مجوز

این ماژول تحت مجوز GNU General Public License version 2 or later منتشر شده است.

## 🙏 حمایت

اگر این ماژول برای شما مفید بود:
- ⭐ به repository ستاره بدهید
- 🐛 باگ‌ها را گزارش دهید
- 💡 پیشنهادات خود را ارائه دهید
- 🔄 به دیگران معرفی کنید

---

**ساخته شده با ❤️ برای جامعه Joomla**
