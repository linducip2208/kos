<?php

namespace App\Services\Seo;

class PseoDataService
{
    public static function cities(): array
    {
        return [
            'jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'malang', 'medan', 'makassar',
            'palembang', 'depok', 'tangerang', 'bekasi', 'bogor', 'denpasar', 'balikpapan', 'manado',
            'pekanbaru', 'padang', 'solo', 'purwokerto', 'cirebon', 'tasikmalaya', 'magelang', 'salatiga',
            'bandar-lampung', 'bengkulu', 'jambi', 'pontianak', 'banjarmasin', 'samarinda', 'tarakan',
            'mataram', 'kupang', 'ambon', 'jayapura', 'manokwari', 'sorong', 'merauke', 'ternate',
            'gorontalo', 'palu', 'kendari', 'mamuju', 'pangkal-pinang', 'tanjung-pinang', 'batam',
            'dumai', 'bukittinggi', 'payakumbuh', 'sawahlunto', 'padang-panjang', 'solok', 'pariaman',
            'batu-sangkar', 'lubuk-linggau', 'pagar-alam', 'prabumulih', 'baturaja', 'lahat', 'muara-enim',
            'sekayu', 'martapura', 'tanjung', 'barabai', 'kandangan', 'rantau', 'amuntai', 'negara',
            'pelaihari', 'marabahan', 'kotabaru', 'batulicin', 'sampit', 'pangkalan-bun', 'kuala-kapuas',
            'buntok', 'tamiang-layang', 'puruk-cahu', 'muara-teweh', 'kuala-kurun', 'kasongan',
            'palangkaraya', 'kuala-pembuang', 'sukamara', 'nanga-bulik', 'putussibau', 'sintang',
            'sekadau', 'sanggau', 'ngabang', 'mempawah', 'singkawang', 'pemangkat', 'sambas',
            'bengkayang', 'kapuas-hulu', 'kubu-raya', 'ketapang', 'kayong-utara', 'landak',
            'melawi', 'landak', 'teluk-keramat', 'tebas', 'jawai', 'selakau', 'pemangkat',
            'singkawang', 'sambas', 'bengkayang', 'ngabang', 'sanggau', 'sekadau', 'sintang',
            'putussibau', 'nanga-pinoh', 'sukadana', 'ketapang', 'kayong-utara',
            'sukabumi', 'cianjur', 'garut', 'tasikmalaya-kota', 'ciamis', 'pangandaran',
            'banjar', 'kuningan', 'majalengka', 'sumedang', 'indramayu', 'subang', 'purwakarta',
            'karawang', 'cikarang', 'cibinong', 'cileungsi', 'gunung-putri', 'citeureup',
            'cimahi', 'lembang', 'parongpong', 'soreang', 'banjaran', 'ciparay', 'majalaya',
            'rancaekek', 'jatinangor', 'tanjungsari', 'cicalengka', 'nagreg', 'garut-kota',
            'karangpawitan', 'leles', 'kadungora', 'wanaraja', 'malangbong', 'cibatu',
            'singaparna', 'manonjaya', 'ciawi', 'cipatujah', 'karangnunggal', 'salopa',
            'bojonggambir', 'sodonghilir', 'taraju', 'salawu', 'pagerageung', 'sukaraja',
            'cipeundeuy', 'cisarua', 'parungponteng', 'karangjaya', 'cisayong', 'bojongasih',
            'rajapolah', 'jamanis', 'sukaratu', 'cihideung', 'indihiang', 'tawang',
            'cibeureum', 'mangkubumi', 'kawalu', 'bantar-kalong', 'bojong', 'bungursari',
            'cibeureum-kota', 'cipedes', 'purbaratu', 'tamansari', 'sukamaju-kidul',
            'sukasari', 'ciledug', 'kadipaten', 'pagerageung-tasik', 'sariwangi',
            'padakembang', 'sukarame', 'sukaraja-tasik', 'sukaresik', 'tanjungjaya',
            'sukaresmi', 'cisompet', 'pakenjeng', 'caringin-kota', 'cikajang', 'banyuresmi',
            'bayongbong', 'blubur-limbangan', 'caringin-garut', 'cibiuk', 'cigedug',
            'cihurip', 'cikembar', 'cikidang', 'ciracap', 'cireunghas', 'cisalak',
            'citamiang', 'gunung-guruh', 'jampang-kulon', 'jampang-tengah',
            'kabandungan', 'kadudampit', 'kalapa-nunggal', 'kebonpedes',
            'lengkong', 'nagrak', 'nya', 'nymplung', 'pabuaran', 'parakan-salak',
            'parung-kuda', 'pelabuhan-ratu', 'purabaya', 'sagaranten', 'simpang',
            'sukalarang', 'sukamantri', 'sukamulya', 'sukanagara', 'sukaraja-smi',
            'surade', 'tegalbuleud', 'waluran', 'warung-kiara', 'bojong-genth',
            'caringin-smi', 'cicantayan', 'cidadap', 'cidolog', 'cisaat',
            'cisitu', 'citamiang', 'dadahup', 'darmaraja', 'gekbrong',
            'genteng', 'gegerbitung', 'gununghalu', 'ibun', 'jalancagak',
            'kertasari', 'kutawaringin', 'margaasih', 'margahayu', 'nagreg-bandung',
            'pacitan', 'ponorogo', 'trenggalek', 'tulungagung', 'blitar',
            'kediri', 'malang-kota', 'lumajang', 'jember', 'banyuwangi',
            'bondowoso', 'situbondo', 'probolinggo', 'pasuruan', 'sidoarjo',
            'mojokerto', 'jombang', 'nganjuk', 'madiun', 'magetan',
            'ngawi', 'bojonegoro', 'tuban', 'lamongan', 'gresik',
            'bangkalan', 'sampang', 'pamekasan', 'sumenep', 'batu',
            'kota-mojokerto', 'kota-pasuruan', 'kota-probolinggo', 'kota-blitar',
            'kota-madiun', 'kota-surabaya', 'kota-batu',
            'bantul', 'sleman', 'kulon-progo', 'gunung-kidul',
            'klaten', 'sukoharjo', 'wonogiri', 'karanganyar', 'sragen',
            'grobogan', 'blora', 'rembang', 'pati', 'kudus', 'jepara',
            'demak', 'temanggung', 'kendal', 'batang', 'pekalongan', 'pemalang',
            'tegal', 'brebes', 'purworejo', 'wonosobo', 'banjarnegara',
            'purbalingga', 'banyumas', 'cilacap', 'kebumen',
            'bima', 'dompu', 'sumbawa', 'sumbawa-barat', 'lombok-tengah',
            'lombok-timur', 'lombok-barat', 'lombok-utara', 'sumbawa-besar',
            'tabanan', 'badung', 'gianyar', 'klungkung', 'bangli',
            'karangasem', 'buleleng', 'jembrana',
            'kupang-kota', 'timor-tengah-selatan', 'timor-tengah-utara',
            'belu', 'alor', 'lembata', 'flores-timur', 'sikka', 'ende',
            'ngada', 'manggarai', 'rote-ndao', 'manggarai-barat',
            'sumba-timur', 'sumba-barat', 'sumba-tengah', 'sumba-barat-daya',
            'malaka', 'sabu-raijua',
            'deli-serdang', 'langkat', 'karo', 'simalungun', 'asahan',
            'labuhanbatu', 'tapanuli-utara', 'tapanuli-tengah', 'tapanuli-selatan',
            'nihil', 'mandailing-natal', 'padang-lawas', 'padang-lawas-utara',
            'serdang-bedagai', 'batubara', 'pakpak-bharat', 'humbang-hasundutan',
            'samosir', 'toba', 'labuhanbatu-utara', 'labuhanbatu-selatan',
            'binjai', 'pematang-siantar', 'tebing-tinggi', 'tanjung-balai',
            'sibolga', 'padang-sidempuan', 'gunungsitoli',
            'buleleng', 'tabanan', 'gianyar', 'amlapura', 'negara',
            'singaraja', 'mangupura', 'bangli-kota', 'semarapura',
            'sigli', 'lhokseumawe', 'langsa', 'meulaboh', 'tapaktuan',
            'blangkejeren', 'kutacane', 'takengon', 'calang', 'sinabang',
            'subulussalam', 'singkil', 'blangpidie', 'sigli-kota', 'sabang',
            'banda-aceh', 'meureudu', 'jeunieb', 'peusangan', 'kuta-blang',
            'sigli', 'reuleuet', 'baktiya', 'tanah-pasir', 'bireuen',
            'pidie', 'aceh-utara', 'aceh-timur', 'aceh-tengah', 'aceh-barat',
            'aceh-selatan', 'aceh-tenggara', 'aceh-jaya', 'aceh-barat-daya',
            'gayo-lues', 'aceh-tamiang', 'nagan-raya', 'aceh-singkil',
            'pidie-jaya', 'bener-meriah',
            'pasuruan-kota',
            'cilegon', 'serang', 'pandeglang', 'lebak', 'tigaraksa',
            'rangkasbitung', 'kresek', 'balaraja', 'curug', 'tigaraksa-kota',
            'cikupa', 'pasar-kemis', 'teluknaga', 'kosambi', 'sepatan',
            'anyer', 'carita', 'labuan', 'panimbang', 'munjul',
            'bayah', 'malingping', 'cibadak', 'ciomas', 'petir',
            'cipanas-lebak', 'sajira', 'cimarga', 'leuwidamar', 'bojongmanik',
            'panggarangan', 'cigemblong', 'cibeber', 'waringinkurung', 'gunung-kencana',
            'wanasalam', 'sobang', 'curugbitung', 'maya', 'cibaliung',
            'cigeulis', 'cikeusik', 'cimanggu', 'saketi', 'pagelaran',
            'patia', 'sukaresmi-pandeglang', 'jiput', 'mandalawangi', 'menes',
            'banjar-pdg', 'cadasari', 'karang-tanjung', 'koroncong', 'majau',
            'pulosari', 'anggasana', 'picung', 'bojong-pdg', 'saketi-kota',
            'cimanuk', 'kaduhejo', 'mekarjaya', 'cimanuk-pandeglang',
            'sukaresmi', 'cimanuk',
        ];
    }

    public static function campuses(): array
    {
        return [
            'ui', 'itb', 'ugm', 'unair', 'undip', 'ub', 'its', 'unpad', 'ipb', 'uns', 'unnes',
            'unhas', 'unud', 'unri', 'unsri', 'usu', 'unand', 'unib', 'untan', 'unmul',
            'unram', 'uncen', 'unsoed', 'unila', 'untirta', 'upi', 'unj', 'uin-jakarta',
            'uin-bandung', 'uin-malang', 'uin-yogya', 'uin-surabaya', 'uin-medan', 'uin-makassar',
            'uin-pekanbaru', 'uin-palembang', 'iain', 'stain', 'stis', 'stan',
            'telkom-university', 'binus', 'gunadarma', 'trisakti', 'tarumanagara',
            'atmajaya', 'petra', 'maranatha', 'parahyangan', 'prasetya-mulya',
            'podomoro', 'sgu', 'brawijaya-malang', 'politeknik-negeri-jakarta',
            'politeknik-negeri-bandung', 'polban', 'pens', 'its-surabaya',
            'polinema', 'politeknik-negeri-semarang', 'polines',
            'umm', 'ums', 'umy', 'umsida', 'umi', 'unismuh',
            'ubaya', 'unika-soegijapranata', 'usd', 'sanata-dharma',
            'president-university', 'universitas-ciputra', 'uc', 'petra-christian',
            'president', 'ukrida', 'uk-petra', 'udayana', 'unwira',
            'politeknik-negeri-medan', 'politeknik-negeri-padang',
            'politeknik-negeri-malang', 'politeknik-negeri-bali',
        ];
    }

    public static function facilities(): array
    {
        return [
            'ac', 'wifi', 'kamar-mandi-dalam', 'dapur-bersama', 'parkir-motor',
            'parkir-mobil', 'tv-kabel', 'spring-bed', 'lemari', 'meja-belajar',
            'laundry', 'kulkas', 'water-heater', 'balkon', 'rooftop',
            'gym', 'kolam-renang', 'cctv', 'satpam-24-jam', 'akses-kunci-card',
            'jemuran', 'taman', 'ruang-tamu', 'dapur-pribadi', 'kipas-angin',
        ];
    }

    public static function priceRanges(): array
    {
        return [
            'dibawah-500rb' => [0, 500000],
            '500rb-1jt' => [500000, 1000000],
            '1jt-15jt' => [1000000, 1500000],
            '15jt-2jt' => [1500000, 2000000],
            '2jt-3jt' => [2000000, 3000000],
            'diatas-3jt' => [3000000, 999999999],
        ];
    }

    public static function types(): array
    {
        return [
            'putra', 'putri', 'campur', 'harian', 'bulanan', 'tahunan',
            'pegawai', 'mahasiswa', 'keluarga', 'eksklusif',
        ];
    }
}
