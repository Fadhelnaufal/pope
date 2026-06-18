Markdown
# EduChem LMS 🧪

EduChem adalah platform *Learning Management System* (LMS) interaktif yang dirancang khusus untuk pembelajaran Kimia SMA. Sistem ini mengimplementasikan metode pedagogi **POE (Predict-Observe-Explain)** untuk membantu siswa memahami konsep kimia secara mendalam melalui alur kerja yang sistematis.

## 🚀 Fitur Utama

- **Metode POE:** Alur pembelajaran terstruktur untuk menstimulasi pemikiran kritis siswa.
- **Dynamic Content Builder:** Guru dapat dengan mudah menyusun materi (Teks, Gambar, H5P) dan soal evaluasi (Pilgan, Esai, Upload File).
- **Forum Diskusi:** Fitur diskusi per-fase yang dikelola Guru untuk memicu interaksi dua arah.
- **AI Tutor Chatbot:** Pendamping belajar bertenaga AI untuk memberikan panduan (dengan fitur *Kill-Switch* untuk menonaktifkan AI saat ujian).
- **Sistem Evaluasi:** Penilaian otomatis untuk soal objektif dan *feedback* AI untuk soal esai.

## 🛠 Teknologi yang Digunakan

**Backend:**
- Laravel 13, PHP 8.2+
- PostgreSQL / MySQL
- Laravel Queue (Redis) untuk background processing

**Frontend:**
- Vue.js 3 (Composition API)
- Inertia.js
- Tailwind CSS
- shadcn-vue & Lucide Icons

**Integrasi AI:**
- Google Gemini AI (Agentic AI)

---

## 📋 Prasyarat Sistem
Pastikan perangkat lunak berikut sudah terinstal di komputer Anda:
- **PHP** >= 8.2
- **Composer**
- **Node.js** (v18+) & **NPM**
- **MySQL** / PostgreSQL
- **Redis** (Untuk Queue)

---

## 🛠️ Panduan Instalasi

**1. Clone Repositori**
```bash
git clone [https://github.com/Fadhelnaufal/pope.git](https://github.com/Fadhelnaufal/pope.git)
cd pope
```
2. Install Dependencies
```Bash
composer install
npm install
```
3. Konfigurasi Environment
Salin file environment:
```Bash
cp .env.example .env
```
Buka file .env dan sesuaikan konfigurasi berikut:
```Code snippet
DB_CONNECTION=mysql
DB_DATABASE=lms_educhem
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
GEMINI_API_KEY=masukkan_api_key_gemini_anda
```
4. Setup Database & Storage
```Bash
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed
```
💻 Cara Menjalankan Aplikasi
Untuk menjalankan aplikasi secara penuh (termasuk fitur AI Chatbot), Anda perlu menjalankan 3 terminal secara bersamaan:
Terminal 1 (Backend Server):
```Bash
php artisan serve
```
Terminal 2 (Frontend/Vite):
```Bash
npm run dev
```
Terminal 3 (Background Worker - Wajib untuk Chatbot AI):
```Bash
php artisan queue:work --timeout=120
```
[ ] Dashboard Analitik Guru
👥 Kontributor
Fadhel Naufal — Lead Developer
