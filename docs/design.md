# Design System dan Konsep Visual

**Project:** dkwibowo  
**Status:** Baseline disepakati  
**Reference:** `docs/example/home.html`  
**Bahasa:** Indonesia dan English

## 1. Design Direction

dkwibowo menggunakan arah visual **dark technical editorial portfolio**.

Karakter utama:

- profesional, teknis, dan personal;
- dark interface dengan aksen biru elektrik;
- tipografi besar dengan hierarchy yang kuat;
- layout editorial yang rapi, bukan dashboard yang padat;
- visual project menjadi fokus utama;
- motion dan parallax dipakai untuk memberi kedalaman dan ritme halaman.

Desain harus terasa seperti portfolio seorang engineer yang membangun sistem serius, tetapi tetap mudah dibaca oleh pengunjung non-teknis.

## 2. Brand Identity

Brand final:

```text
dkwibowo
```

Nama brand harus digunakan secara konsisten pada:

- logo atau wordmark;
- navigation bar;
- footer;
- title dan metadata SEO jika relevan;
- asset dan URL public.

Nama personal dapat digunakan sebagai bagian dari isi hero atau profil, tetapi bukan sebagai brand alternatif.

Brand `ALVALENS` dan identitas contoh lain dari reference HTML tidak digunakan pada implementasi final.

## 3. Design Principles

### 3.1 Content first

Visual mendukung project dan cerita di baliknya. Efek visual tidak boleh menghalangi judul, deskripsi, navigasi, atau CTA.

### 3.2 Technical but human

Elemen seperti monospace label, grid, metadata, dan struktur modular boleh digunakan, tetapi bahasa dan konten tetap terasa personal.

### 3.3 Calm dark interface

Latar gelap menjadi fondasi. Kontras dibangun melalui surface, border halus, whitespace, dan aksen biru—bukan melalui banyak warna terang.

### 3.4 Progressive disclosure

Informasi utama terlihat segera. Detail tambahan dapat muncul melalui hover, motion, atau halaman detail project.

### 3.5 Bilingual by URL

Bahasa ditentukan oleh URL:

```text
/id/...
/en/...
```

Language switcher harus mempertahankan konteks halaman dan query parameter yang relevan.

## 4. Information Architecture

Struktur menu yang disepakati:

```text
Work
Skills
Experience
Project
About
Contact
```

`Work` menjadi anchor ke section project pada homepage. `Project` menjadi halaman archive seluruh content project yang published.

## 5. Page Composition

Komposisi umum halaman public:

```text
Global Navigation
    ↓
Page Header / Hero
    ↓
Primary Content
    ↓
Supporting Content
    ↓
Call to Action
    ↓
Footer
```

Homepage final yang disepakati:

```text
Perkenalan + profile image
Skills
Experience
Maximum 5 project terbaru
Contact
```

Project homepage diurutkan berdasarkan project terbaru dan hanya mengambil maksimal lima content published.

### Hero copy proposal

Bahasa hero utama menggunakan English. Draft copy:

```text
Eyebrow: FULL-STACK ENGINEER · AI
Heading: Building intelligent systems with AI and scalable engineering
Body: I design and build reliable digital products, from complex data systems to thoughtful user experiences.
Primary CTA: Get in touch
Secondary CTA: View selected work
```

Nama personal tampil sebagai identitas hero, sementara `dkwibowo` tetap menjadi brand utama. Copy ini sudah disetujui.

## 6. Layout

### Container

- maximum width: sekitar `1280px`;
- horizontal gutter desktop: sekitar `24px`;
- horizontal margin mobile: sekitar `20px`;
- section spacing besar untuk menjaga ritme editorial;
- content width dibatasi agar paragraf tetap nyaman dibaca.

### Grid

Gunakan grid 12 kolom pada desktop untuk area project atau feature.

Pada mobile, seluruh layout berubah menjadi satu kolom dengan urutan konten yang eksplisit.

### Project layout

Project dapat ditampilkan dengan bento grid:

- satu project utama berukuran besar;
- beberapa project pendukung;
- variasi ukuran berdasarkan prioritas editorial;
- setiap card memiliki image, project type, title, dan ringkasan singkat.

Bento grid bukan kewajiban untuk setiap halaman. Jika jumlah project sedikit, layout list atau feature-detail lebih tepat.

## 7. Color Tokens

### Core palette

```text
background       #10131A
surface          #1D2027
surface-high     #272A31
surface-lowest   #0B0E15
border           #424754
text-primary     #E1E2EC
text-secondary   #C2C6D6
text-muted       #8C909F
accent-blue      #ADC6FF
accent-blue-strong #4D8EFF
```

### Usage

- `background`: page background;
- `surface`: card dan panel;
- `surface-high`: hover dan elevated element;
- `text-primary`: heading dan informasi utama;
- `text-secondary`: body text penting;
- `text-muted`: metadata dan supporting text;
- `accent-blue`: link, CTA, active state, dan brand highlight;
- `accent-blue-strong`: focus state atau emphasis yang lebih kuat.

Hijau dan oranye dari reference hanya boleh digunakan sebagai semantic accent jika ada kebutuhan konten yang jelas. Keduanya bukan warna utama brand.

## 8. Typography

Font utama yang disarankan:

```text
Primary sans-serif: Manrope atau Be Vietnam Pro
Technical accent: JetBrains Mono
```

Aturan:

- gunakan satu primary sans-serif secara konsisten;
- gunakan JetBrains Mono untuk label, metadata, tag, dan technical notation;
- hindari mencampur Inter, Manrope, dan Be Vietnam Pro sekaligus;
- heading besar boleh menggunakan tracking negatif secara moderat;
- body text harus memiliki line-height yang longgar;
- label uppercase hanya untuk metadata pendek, bukan paragraf.

## 9. Component Language

### Navigation

- fixed atau sticky pada desktop;
- background semi-transparan ketika scroll;
- border bawah tipis;
- active dan hover state menggunakan aksen biru;
- mobile navigation harus benar-benar interaktif dan memiliki accessible label.

### Button

Primary button:

- background aksen biru;
- teks gelap dengan kontras tinggi;
- radius kecil hingga medium;
- hover menggunakan perubahan brightness atau transform kecil.

Secondary button/link:

- transparent atau surface background;
- border halus;
- tidak boleh bersaing dengan primary CTA.

### Glass panel

Glass panel digunakan secara selektif pada:

- hero badge;
- project card;
- feature card;
- elevated navigation.

Glass effect tidak digunakan pada semua container agar hierarchy tetap terbaca dan performa tetap baik.

### Tags and metadata

- bentuk compact;
- monospace atau label typography;
- uppercase hanya bila panjang teks pendek;
- warna muted dengan aksen biru untuk selected/active state.

### Project card

Minimum content:

- project title;
- project type;
- short description;
- cover atau visual fallback;
- link ke detail project.

Cover project berasal dari upload admin melalui `cover_image`. Fallback visual hanya digunakan jika cover belum tersedia.

Hover dapat menampilkan deskripsi atau memperjelas image, tetapi informasi penting tidak boleh hanya tersedia melalui hover.

## 10. Motion and Parallax

Motion dan parallax dipertahankan sebagai bagian dari karakter desain.

Motion yang disetujui:

- scroll reveal untuk section dan card;
- staggered entrance untuk elemen yang berdekatan;
- parallax ringan pada background abstraction dan hero image;
- hover translation kecil pada card;
- perubahan opacity dan scale pada project image;
- navbar transition saat scroll.

Batasan:

- motion tidak boleh mengubah layout secara tiba-tiba;
- tidak boleh memakai animasi tanpa batas pada konten utama;
- parallax harus ringan dan tidak mengganggu pembacaan;
- `prefers-reduced-motion: reduce` wajib dihormati;
- CTA dan navigasi harus tetap usable tanpa motion.

## 11. Responsive Behavior

### Desktop

- navigation lengkap;
- hero dapat menggunakan dua kolom;
- project menggunakan bento atau multi-column grid;
- timeline atau contextual section dapat menggunakan horizontal alignment.

### Mobile

- navigation berubah menjadi menu button;
- hero menjadi satu kolom;
- heading diturunkan ukurannya tanpa kehilangan hierarchy;
- bento grid menjadi list atau single-column cards;
- hover-only content harus tersedia secara default;
- parallax dikurangi intensitasnya bila diperlukan.

## 12. Accessibility

Design implementation wajib menyediakan:

- semantic heading order;
- visible keyboard focus state;
- accessible label untuk icon-only button/link;
- kontras teks yang memadai;
- navigasi mobile yang dapat digunakan keyboard;
- alternative text untuk image;
- reduced motion support;
- informasi penting tidak bergantung pada hover atau animasi.

## 13. Content and Application Mapping

Project pada halaman public berasal dari content published pada aplikasi.

Mapping awal:

```text
project title      → title_id / title_en
project type       → project_type
project summary    → Markdown yang sudah dirender atau metadata turunan
project image      → cover_image
project tags       → content_tags
project detail     → Markdown per language
```

Content draft, archived, dan trashed tidak boleh muncul pada komponen public.

## 14. Asset and Performance Rules

- asset production disimpan lokal atau melalui pipeline asset yang terkontrol;
- hindari ketergantungan Tailwind CDN untuk production;
- font tidak boleh di-import berulang dari provider yang sama;
- image harus memiliki ukuran dan format yang sesuai;
- gunakan lazy loading untuk image non-hero;
- gunakan fallback image yang konsisten;
- remote placeholder image dari reference tidak digunakan pada production.

## 15. Profile Image

`assets/img/profile.webp` digunakan sebagai profile image hero.

Image tidak diubah secara permanen pada asset source. Penyesuaian dilakukan pada frontend melalui:

- responsive aspect ratio;
- `object-fit` dan `object-position`;
- ukuran dan crop berbeda pada desktop/mobile bila diperlukan;
- overlay atau treatment visual yang tetap menjaga keterbacaan wajah.

## 16. CTA dan Social Links

CTA utama mengarah ke:

- email;
- WhatsApp.

Contact values:

```text
Email: me@dkwibowo.my.id
WhatsApp: +62811-341-6622
```

WhatsApp URL menggunakan format internasional tanpa tanda `+`:

```text
https://wa.me/628113416622
```

Social link yang valid:

- [LinkedIn](https://www.linkedin.com/in/dirgahayu/);
- [GitHub](https://github.com/dirg-bisma).

Link social lain tidak ditampilkan sampai URL valid tersedia.

## 17. Open Decisions

Hal berikut perlu dibahas sebelum implementasi visual final:

Tidak ada keputusan desain utama yang tertunda. `Skills`, `Experience`, dan `About` ditetapkan sebagai section statis.
