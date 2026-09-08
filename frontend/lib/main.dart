import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ── Brand ────────────────────────────────────────────────────────────────────
const kPrimary   = Color(0xFFE53935);
const kAccent    = Color(0xFFFB8C00);
const kDark      = Color(0xFF1E293B);
const kLight     = Color(0xFFFFF8F4);
const kSurface   = Color(0xFFFFFFFF);
const kMuted     = Color(0xFF94A3B8);
const kGreen     = Color(0xFF22C55E);
const kBg        = Color(0xFFF1F5F9);

const kApiBase = 'http://localhost:8000/api';

// ── Global Auth ──────────────────────────────────────────────────────────────
class Auth extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _user;

  bool get isLoggedIn => _token != null;
  String get role => _user?['role']?['name'] ?? 'USER';
  String get name => _user?['name'] ?? 'Guest';
  Map<String, dynamic>? get user => _user;

  Map<String, String> get _h => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  void setAuth(String token, Map<String, dynamic> user) {
    _token = token;
    _user = user;
    notifyListeners();
  }

  Future<void> loadToken() async {
    final sp = await SharedPreferences.getInstance();
    _token = sp.getString('token');
    final raw = sp.getString('user');
    if (raw != null) _user = jsonDecode(raw);
    notifyListeners();
  }

  Future<void> saveToken(String token, Map<String, dynamic> user) async {
    final sp = await SharedPreferences.getInstance();
    await sp.setString('token', token);
    await sp.setString('user', jsonEncode(user));
    setAuth(token, user);
  }

  Future<void> clearToken() async {
    final sp = await SharedPreferences.getInstance();
    await sp.remove('token');
    await sp.remove('user');
    _token = null;
    _user = null;
    notifyListeners();
  }

  Future<Map<String, dynamic>> api(String method, String path, {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('$kApiBase$path');
    http.Response res;
    switch (method) {
      case 'POST': res = await http.post(uri, headers: _h, body: body != null ? jsonEncode(body) : null); break;
      case 'PATCH': res = await http.patch(uri, headers: _h, body: body != null ? jsonEncode(body) : null); break;
      case 'DELETE': res = await http.delete(uri, headers: _h); break;
      default: res = await http.get(uri, headers: _h); break;
    }
    final data = jsonDecode(res.body);
    if (res.statusCode >= 400) throw Exception(data['message'] ?? 'Gagal');
    return data;
  }
}

late final Auth auth;

// ── Main ─────────────────────────────────────────────────────────────────────
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  auth = Auth();
  await auth.loadToken();
  runApp(const App());
}

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: auth,
      builder: (_, __) => MaterialApp.router(
        title: 'Titip Kilat',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          scaffoldBackgroundColor: kBg,
          colorScheme: ColorScheme.fromSeed(seedColor: kPrimary, primary: kPrimary, secondary: kAccent),
          appBarTheme: const AppBarTheme(backgroundColor: kSurface, foregroundColor: kDark, elevation: 0, scrolledUnderElevation: 1),
          cardTheme: CardThemeData(color: kSurface, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16))),
        ),
        routerConfig: _router,
      ),
    );
  }
}

final GoRouter _router = GoRouter(
  initialLocation: '/',
  refreshListenable: auth,
  redirect: (_, state) {
    final logged = auth.isLoggedIn;
    final pub = ['/', '/login', '/register'];
    if (!logged && !pub.contains(state.uri.path)) return '/login';
    if (logged && pub.contains(state.uri.path)) return '/home';
    return null;
  },
  routes: [
    GoRoute(path: '/', builder: (_, __) => const SplashScreen()),
    GoRoute(path: '/login', builder: (_, __) => const LoginPage()),
    GoRoute(path: '/register', builder: (_, __) => const RegisterPage()),
    ShellRoute(builder: (_, __, child) => AppShell(child: child), routes: [
      GoRoute(path: '/home', builder: (_, __) => const HomePage()),
      GoRoute(path: '/orders', builder: (_, __) => const OrdersPage()),
      GoRoute(path: '/orders/new', builder: (_, __) => const NewOrderPage()),
      GoRoute(path: '/wallet', builder: (_, __) => const WalletPage()),
      GoRoute(path: '/trips', builder: (_, __) => const TripsPage()),
      GoRoute(path: '/admin', builder: (_, __) => const AdminPage()),
      GoRoute(path: '/admin/disputes', builder: (_, __) => const AdminDisputesPage()),
      GoRoute(path: '/superadmin', builder: (_, __) => const SuperAdminPage()),
      GoRoute(path: '/superadmin/fees', builder: (_, __) => const FeesPage()),
      GoRoute(path: '/superadmin/ledger', builder: (_, __) => const LedgerPage()),
      GoRoute(path: '/superadmin/monitor', builder: (_, __) => const MonitorPage()),
    ]),
  ],
);

// ── Splash ───────────────────────────────────────────────────────────────────
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override State<SplashScreen> createState() => _SplashScreenState();
}
class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 800), () {
      if (mounted) context.go(auth.isLoggedIn ? '/home' : '/login');
    });
  }
  @override
  Widget build(BuildContext context) => const Scaffold(
    body: Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(Icons.local_shipping_rounded, size: 64, color: kPrimary),
      SizedBox(height: 16),
      Text('Titip Kilat', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: kPrimary)),
    ])),
  );
}

// ── App Shell (Drawer + Bottom Nav) ──────────────────────────────────────────
class AppShell extends StatelessWidget {
  final Widget child;
  const AppShell({required this.child, super.key});

  int _navIdx(BuildContext c) {
    final p = GoRouterState.of(c).uri.path;
    if (p.startsWith('/orders')) return 1;
    if (p.startsWith('/wallet')) return 2;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Titip Kilat', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [
          Builder(builder: (ctx) => IconButton(
            icon: const Icon(Icons.menu_rounded),
            onPressed: () => Scaffold.of(ctx).openEndDrawer(),
          )),
        ],
      ),
      endDrawer: Drawer(
        backgroundColor: kSurface,
        child: SafeArea(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: const BoxDecoration(gradient: LinearGradient(colors: [kPrimary, kAccent])),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              CircleAvatar(radius: 28, backgroundColor: Colors.white.withAlpha(40), child: const Icon(Icons.person, color: kSurface, size: 32)),
              const SizedBox(height: 12),
              Text(auth.name, style: const TextStyle(color: kSurface, fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 4),
              Text(auth.role, style: TextStyle(color: Colors.white.withAlpha(180), fontSize: 12)),
            ]),
          ),
          const SizedBox(height: 8),
          _drawerItem(context, Icons.dashboard_rounded, 'Dashboard', () { Navigator.pop(context); context.go('/home'); }),
          _drawerItem(context, Icons.receipt_long_rounded, 'Pesanan', () { Navigator.pop(context); context.go('/orders'); }),
          _drawerItem(context, Icons.account_balance_wallet_rounded, 'E-Wallet', () { Navigator.pop(context); context.go('/wallet'); }),
          if (auth.role == 'TRAVELER') _drawerItem(context, Icons.flight_rounded, 'Trip Saya', () { Navigator.pop(context); context.go('/trips'); }),
          if (auth.role == 'ADMIN') _drawerItem(context, Icons.admin_panel_settings_rounded, 'Admin Panel', () { Navigator.pop(context); context.go('/admin'); }),
          if (auth.role == 'SUPER_ADMIN') _drawerItem(context, Icons.security_rounded, 'Super Admin', () { Navigator.pop(context); context.go('/superadmin'); }),
          const Spacer(),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.logout_rounded, color: kPrimary),
            title: const Text('Keluar', style: TextStyle(color: kPrimary)),
            onTap: () { auth.clearToken(); Navigator.pop(context); context.go('/login'); },
          ),
        ])),
      ),
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: _navIdx(context),
        onDestinationSelected: (i) => context.go(['/home', '/orders', '/wallet'][i]),
        backgroundColor: kSurface,
        indicatorColor: kPrimary.withAlpha(25),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_rounded), label: 'Beranda'),
          NavigationDestination(icon: Icon(Icons.receipt_long_rounded), label: 'Pesanan'),
          NavigationDestination(icon: Icon(Icons.account_balance_wallet_rounded), label: 'Wallet'),
        ],
      ),
    );
  }

  Widget _drawerItem(BuildContext c, IconData icon, String label, VoidCallback onTap) {
    return ListTile(leading: Icon(icon, color: kDark), title: Text(label, style: const TextStyle(fontWeight: FontWeight.w500)), onTap: onTap);
  }
}

// ── Login Page ───────────────────────────────────────────────────────────────
class LoginPage extends StatefulWidget {
  const LoginPage({super.key});
  @override State<LoginPage> createState() => _LoginPageState();
}
class _LoginPageState extends State<LoginPage> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _pass = TextEditingController();
  bool _busy = false;
  String? _err;

  Future<void> _do() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _err = null; });
    try {
      final d = await auth.api('POST', '/auth/login', body: {'email': _email.text.trim(), 'password': _pass.text});
      await auth.saveToken(d['token'], Map<String, dynamic>.from(d['user']));
      if (mounted) context.go('/home');
    } catch (e) {
      setState(() => _err = '$e'.replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(child: Center(child: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(color: kPrimary.withAlpha(15), shape: BoxShape.circle),
            child: const Icon(Icons.local_shipping_rounded, size: 48, color: kPrimary),
          ),
          const SizedBox(height: 20),
          const Text('Selamat Datang', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800)),
          const SizedBox(height: 4),
          Text('Masuk ke akun Titip Kilat kamu', style: TextStyle(color: kMuted)),
          const SizedBox(height: 36),
          Form(key: _form, child: Column(children: [
            TextFormField(
              controller: _email,
              decoration: InputDecoration(labelText: 'Email', prefixIcon: const Icon(Icons.email_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
              keyboardType: TextInputType.emailAddress,
              validator: (v) => v != null && v.contains('@') ? null : 'Email tidak valid',
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _pass,
              decoration: InputDecoration(labelText: 'Password', prefixIcon: const Icon(Icons.lock_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
              obscureText: true,
              validator: (v) => v != null && v.length >= 6 ? null : 'Min 6 karakter',
            ),
          ])),
          if (_err != null) ...[const SizedBox(height: 12), Container(
            width: double.infinity, padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: kPrimary.withAlpha(15), borderRadius: BorderRadius.circular(8)),
            child: Text(_err!, style: const TextStyle(color: kPrimary, fontSize: 13)),
          )],
          const SizedBox(height: 24),
          SizedBox(width: double.infinity, height: 52, child: FilledButton(
            onPressed: _busy ? null : _do,
            style: FilledButton.styleFrom(backgroundColor: kPrimary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
            child: _busy ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: kSurface)) : const Text('Masuk', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          )),
          const SizedBox(height: 16),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Text('Belum punya akun?', style: TextStyle(color: kMuted)),
            TextButton(onPressed: () => context.go('/register'), child: const Text('Daftar')),
          ]),
        ]),
      ))),
    );
  }
}

// ── Register Page ────────────────────────────────────────────────────────────
class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});
  @override State<RegisterPage> createState() => _RegisterPageState();
}
class _RegisterPageState extends State<RegisterPage> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _pass = TextEditingController();
  String _role = 'USER';
  bool _busy = false;
  String? _err;

  Future<void> _do() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _err = null; });
    try {
      final d = await auth.api('POST', '/auth/register', body: {
        'name': _name.text.trim(), 'email': _email.text.trim(), 'password': _pass.text, 'role': _role,
      });
      await auth.saveToken(d['token'], Map<String, dynamic>.from(d['user']));
      if (mounted) context.go('/home');
    } catch (e) {
      setState(() => _err = '$e'.replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(child: Center(child: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Text('Buat Akun Baru', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800)),
          const SizedBox(height: 4),
          Text('Gabung dan mulai titip barang!', style: TextStyle(color: kMuted)),
          const SizedBox(height: 36),
          Form(key: _form, child: Column(children: [
            TextFormField(
              controller: _name, validator: (v) => v != null && v.isNotEmpty ? null : 'Wajib diisi',
              decoration: InputDecoration(labelText: 'Nama Lengkap', prefixIcon: const Icon(Icons.person_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _email, keyboardType: TextInputType.emailAddress, validator: (v) => v != null && v.contains('@') ? null : 'Email tidak valid',
              decoration: InputDecoration(labelText: 'Email', prefixIcon: const Icon(Icons.email_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _pass, obscureText: true, validator: (v) => v != null && v.length >= 8 ? null : 'Min 8 karakter',
              decoration: InputDecoration(labelText: 'Password', prefixIcon: const Icon(Icons.lock_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _role,
              decoration: InputDecoration(labelText: 'Daftar sebagai', prefixIcon: const Icon(Icons.badge_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)), filled: true, fillColor: kSurface),
              items: const [
                DropdownMenuItem(value: 'USER', child: Text('👤 Pembeli')),
                DropdownMenuItem(value: 'COURIER', child: Text('🚚 Kurir')),
                DropdownMenuItem(value: 'TRAVELER', child: Text('✈️ Traveler')),
                DropdownMenuItem(value: 'ADMIN', child: Text('🛡️ Admin')),
                DropdownMenuItem(value: 'SUPER_ADMIN', child: Text('⚙️ Super Admin')),
              ],
              onChanged: (v) => setState(() => _role = v ?? 'USER'),
            ),
          ])),
          if (_err != null) ...[const SizedBox(height: 12), Container(
            width: double.infinity, padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: kPrimary.withAlpha(15), borderRadius: BorderRadius.circular(8)),
            child: Text(_err!, style: const TextStyle(color: kPrimary, fontSize: 13)),
          )],
          const SizedBox(height: 24),
          SizedBox(width: double.infinity, height: 52, child: FilledButton(
            onPressed: _busy ? null : _do,
            style: FilledButton.styleFrom(backgroundColor: kPrimary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
            child: _busy ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: kSurface)) : const Text('Daftar Sekarang', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          )),
          const SizedBox(height: 16),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Text('Sudah punya akun?', style: TextStyle(color: kMuted)),
            TextButton(onPressed: () => context.go('/login'), child: const Text('Masuk')),
          ]),
        ]),
      ))),
    );
  }
}

// ── Home Page ────────────────────────────────────────────────────────────────
class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      // Greeting banner
      Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [kPrimary, Color(0xFFEF5350)]),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: kPrimary.withAlpha(40), blurRadius: 16, offset: const Offset(0, 6))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Halo, ${auth.name} 👋', style: const TextStyle(color: kSurface, fontSize: 20, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text('Mau titip apa hari ini?', style: TextStyle(color: Colors.white.withAlpha(200), fontSize: 14)),
          const SizedBox(height: 20),
          Row(children: [
            _pill(Icons.local_shipping_rounded, 'Antar Warga', () => context.go('/orders/new')),
            const SizedBox(width: 10),
            _pill(Icons.flight_rounded, 'Jastip', () => context.go('/trips')),
          ]),
        ]),
      ),
      const SizedBox(height: 24),

      // Quick actions
      const Text('Layanan', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
      const SizedBox(height: 12),
      Row(children: [
        _actionCard(context, Icons.add_shopping_cart_rounded, 'Buat Order', kPrimary, () => context.go('/orders/new')),
        const SizedBox(width: 12),
        _actionCard(context, Icons.receipt_long_rounded, 'Pesanan', kAccent, () => context.go('/orders')),
        const SizedBox(width: 12),
        _actionCard(context, Icons.account_balance_wallet_rounded, 'Wallet', kGreen, () => context.go('/wallet')),
      ]),
      const SizedBox(height: 24),

      // Role-specific section
      if (auth.role == 'USER') _userSection(context),
      if (auth.role == 'COURIER') _courierSection(context),
      if (auth.role == 'TRAVELER') _travelerSection(context),
      if (auth.role == 'ADMIN') _adminSection(context),
      if (auth.role == 'SUPER_ADMIN') _superAdminSection(context),

      // How it works
      const SizedBox(height: 8),
      const Text('Cara Kerja', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
      const SizedBox(height: 12),
      _step(1, 'Pesan', 'Pilih layanan & isi detail barang'),
      _step(2, 'Proses', 'Kurir/Traveler mengerjakan pesanan'),
      _step(3, 'Selesai', 'Barang sampai & pembayaran otomatis'),
    ]);
  }

  static Widget _pill(IconData icon, String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(color: Colors.white.withAlpha(30), borderRadius: BorderRadius.circular(24)),
        child: Row(mainAxisSize: MainAxisSize.min, children: [Icon(icon, color: kSurface, size: 18), const SizedBox(width: 8), Text(label, style: const TextStyle(color: kSurface, fontWeight: FontWeight.w600))]),
      ),
    );
  }

  static Widget _actionCard(BuildContext c, IconData icon, String label, Color color, VoidCallback onTap) {
    return Expanded(child: GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20),
        decoration: BoxDecoration(color: kSurface, borderRadius: BorderRadius.circular(16), border: Border.all(color: kBg)),
        child: Column(children: [Icon(icon, color: color, size: 28), const SizedBox(height: 8), Text(label, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13))]),
      ),
    ));
  }

  static Widget _step(int n, String title, String desc) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(children: [
        Container(width: 32, height: 32, decoration: BoxDecoration(color: kPrimary.withAlpha(15), shape: BoxShape.circle), child: Center(child: Text('$n', style: const TextStyle(color: kPrimary, fontWeight: FontWeight.w700)))),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w600)), Text(desc, style: TextStyle(color: kMuted, fontSize: 13))]))
      ]),
    );
  }

  static Widget _userSection(BuildContext c) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    const Text('Menu Cepat', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
    const SizedBox(height: 12),
    _tile(Icons.add_location_alt_rounded, 'Buat Order Antar Warga', 'Kirim barang dalam kota', kPrimary, () => c.go('/orders/new')),
    _tile(Icons.flight_rounded, 'Pre-Order Internasional', 'Belanja dari luar negeri', kAccent, () => c.go('/trips')),
    _tile(Icons.star_rounded, 'Rating & Review', 'Beri penilaian kurir/traveler', kGreen, null),
  ]);

  static Widget _courierSection(BuildContext c) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    const Text('Panel Kurir', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
    const SizedBox(height: 12),
    _tile(Icons.flash_on_rounded, 'Kurir Rebut', 'Ambil order yang tersedia', kPrimary, () => c.go('/orders')),
    _tile(Icons.camera_alt_rounded, 'Upload Invoice', 'Foto struk belanja', kAccent, null),
    _tile(Icons.local_shipping_rounded, 'Update Pengiriman', 'Ubah status delivery', kGreen, null),
  ]);

  static Widget _travelerSection(BuildContext c) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    const Text('Panel Traveler', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
    const SizedBox(height: 12),
    _tile(Icons.add_circle_rounded, 'Buat Jadwal Trip', 'Buka PO internasional', kPrimary, () => c.go('/trips')),
    _tile(Icons.shopping_bag_rounded, 'Kelola Pre-Order', 'Terima & proses PO', kAccent, () => c.go('/orders')),
    _tile(Icons.local_post_office_rounded, 'Kirim Barang', 'Update pengiriman lintas negara', kGreen, null),
  ]);

  static Widget _adminSection(BuildContext c) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    const Text('Panel Admin', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
    const SizedBox(height: 12),
    _tile(Icons.analytics_rounded, 'Transaksi Aktif', 'Monitor semua order', kPrimary, () => c.go('/admin')),
    _tile(Icons.gavel_rounded, 'Review Disputes', 'Selesaikan konflik', kAccent, () => c.go('/admin/disputes')),
    _tile(Icons.verified_user_rounded, 'Verifikasi KYC', 'Setujui identitas user', kGreen, null),
  ]);

  static Widget _superAdminSection(BuildContext c) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    const Text('Panel Super Admin', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
    const SizedBox(height: 12),
    _tile(Icons.account_balance_rounded, 'Audit Ledger', 'Cek saldo semua wallet', kPrimary, () => c.go('/superadmin/ledger')),
    _tile(Icons.tune_rounded, 'Konfigurasi Fee', 'Ubah biaya platform', kAccent, () => c.go('/superadmin/fees')),
    _tile(Icons.monitor_heart_rounded, 'Monitoring', 'Statistik sistem', kGreen, () => c.go('/superadmin/monitor')),
  ]);

  static Widget _tile(IconData icon, String title, String sub, Color color, VoidCallback? onTap) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Card(child: ListTile(
        onTap: onTap,
        leading: CircleAvatar(backgroundColor: color.withAlpha(18), foregroundColor: color, child: Icon(icon, size: 22)),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
        subtitle: Text(sub, style: TextStyle(color: kMuted, fontSize: 12)),
        trailing: onTap != null ? const Icon(Icons.chevron_right_rounded, size: 20) : null,
      )),
    );
  }
}

// ── Orders Page ──────────────────────────────────────────────────────────────
class OrdersPage extends StatefulWidget {
  const OrdersPage({super.key});
  @override State<OrdersPage> createState() => _OrdersPageState();
}
class _OrdersPageState extends State<OrdersPage> {
  String _filter = 'all';
  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        const Text('Pesanan', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
        if (auth.role == 'USER')
          FilledButton.icon(
            onPressed: () => context.go('/orders/new'),
            icon: const Icon(Icons.add, size: 18),
            label: const Text('Baru'),
            style: FilledButton.styleFrom(backgroundColor: kPrimary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
          ),
      ]),
      const SizedBox(height: 16),
      // Filter chips
      Row(children: ['all', 'active', 'done'].map((f) => Padding(
        padding: const EdgeInsets.only(right: 8),
        child: ChoiceChip(
          label: Text(f == 'all' ? 'Semua' : f == 'active' ? 'Aktif' : 'Selesai'),
          selected: _filter == f,
          onSelected: (_) => setState(() => _filter = f),
          selectedColor: kPrimary.withAlpha(20),
          labelStyle: TextStyle(color: _filter == f ? kPrimary : kMuted),
        ),
      )).toList()),
      const SizedBox(height: 24),
      Center(child: Column(children: [
        Icon(Icons.receipt_long_rounded, size: 56, color: kMuted.withAlpha(60)),
        const SizedBox(height: 12),
        Text('Belum ada pesanan', style: TextStyle(color: kMuted, fontSize: 15)),
        const SizedBox(height: 8),
        if (auth.role == 'USER')
          TextButton(onPressed: () => context.go('/orders/new'), child: const Text('Buat order sekarang')),
      ])),
    ]);
  }
}

// ── New Order Page ───────────────────────────────────────────────────────────
class NewOrderPage extends StatefulWidget {
  const NewOrderPage({super.key});
  @override State<NewOrderPage> createState() => _NewOrderPageState();
}
class _NewOrderPageState extends State<NewOrderPage> {
  final _form = GlobalKey<FormState>();
  final _origin = TextEditingController();
  final _dest = TextEditingController();
  final _weight = TextEditingController();
  final _pickup = TextEditingController();
  final _delivery = TextEditingController();
  final _desc = TextEditingController();
  final _count = TextEditingController(text: '1');
  bool _busy = false;
  String? _err;
  String? _ok;

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _err = null; _ok = null; });
    try {
      final d = await auth.api('POST', '/orders', body: {
        'type': 'ANTAR_WARGA',
        'origin_zone': _origin.text.trim().toUpperCase(),
        'destination_zone': _dest.text.trim().toUpperCase(),
        'weight_lbs': double.parse(_weight.text),
        'pickup_address': _pickup.text.trim(),
        'delivery_address': _delivery.text.trim(),
        'item_description': _desc.text.trim(),
        'item_count': int.parse(_count.text),
      });
      setState(() => _ok = 'Order #${d['order']['id']} berhasil! Status: ${d['order']['status']}');
    } catch (e) {
      setState(() => _err = '$e'.replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      const Text('Order Antar Warga', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
      const SizedBox(height: 4),
      Text('Isi detail barang untuk dikirim kurir lokal', style: TextStyle(color: kMuted)),
      const SizedBox(height: 24),

      // Progress indicator
      Row(children: [
        _circleStep(1, true), _line(true), _circleStep(2, false), _line(false), _circleStep(3, false),
      ]),
      const SizedBox(height: 8),
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: const [
        Text('Detail', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
        Text('Kirim', style: TextStyle(fontSize: 11)),
        Text('Selesai', style: TextStyle(fontSize: 11)),
      ]),
      const SizedBox(height: 28),

      Form(key: _form, child: Column(children: [
        _sectionTitle('Lokasi'),
        Row(children: [
          Expanded(child: _field(_origin, 'Asal (zona)', 'JAKARTA')),
          const SizedBox(width: 12),
          Expanded(child: _field(_dest, 'Tujuan (zona)', 'BANDUNG')),
        ]),
        const SizedBox(height: 16),
        _field(_pickup, 'Alamat penjemputan', 'Jl. Sudirman No.1'),
        const SizedBox(height: 16),
        _field(_delivery, 'Alamat pengiriman', 'Jl. Buah Batu No.2'),

        const SizedBox(height: 24),
        _sectionTitle('Barang'),
        _field(_desc, 'Deskripsi barang', 'Laptop ASUS ROG'),
        const SizedBox(height: 16),
        Row(children: [
          Expanded(child: _field(_weight, 'Berat (lbs)', '2.5', keyboard: TextInputType.number)),
          const SizedBox(width: 12),
          Expanded(child: _field(_count, 'Jumlah', '1', keyboard: TextInputType.number)),
        ]),
      ])),

      if (_err != null) ...[const SizedBox(height: 16), _alert(_err!, kPrimary)],
      if (_ok != null) ...[const SizedBox(height: 16), _alert(_ok!, kGreen)],
      const SizedBox(height: 24),
      SizedBox(width: double.infinity, height: 52, child: FilledButton(
        onPressed: _busy ? null : _submit,
        style: FilledButton.styleFrom(backgroundColor: kPrimary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
        child: _busy ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: kSurface)) : const Text('Buat Order', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
      )),
    ]);
  }

  static Widget _sectionTitle(String t) => Padding(padding: const EdgeInsets.only(bottom: 12), child: Text(t, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)));

  static Widget _field(TextEditingController c, String label, String hint, {TextInputType keyboard = TextInputType.text}) {
    return TextFormField(
      controller: c, keyboardType: keyboard,
      validator: (v) => v != null && v.trim().isNotEmpty ? null : 'Wajib diisi',
      decoration: InputDecoration(labelText: label, hintText: hint, border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)), filled: true, fillColor: kSurface, contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14)),
    );
  }

  static Widget _circleStep(int n, bool active) {
    return Container(width: 32, height: 32, decoration: BoxDecoration(
      color: active ? kPrimary : kPrimary.withAlpha(20), shape: BoxShape.circle,
    ), child: Center(child: Text('$n', style: TextStyle(color: active ? kSurface : kPrimary, fontWeight: FontWeight.w700, fontSize: 13))));
  }

  static Widget _line(bool active) => Expanded(child: Container(height: 2, color: active ? kPrimary : kPrimary.withAlpha(40)));

  static Widget _alert(String msg, Color color) => Container(
    width: double.infinity, padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(color: color.withAlpha(15), borderRadius: BorderRadius.circular(10)),
    child: Row(children: [Icon(color == kGreen ? Icons.check_circle_rounded : Icons.error_rounded, color: color, size: 20), const SizedBox(width: 10), Expanded(child: Text(msg, style: TextStyle(color: color, fontSize: 13)))]),
  );
}

// ── Wallet Page ──────────────────────────────────────────────────────────────
class WalletPage extends StatefulWidget {
  const WalletPage({super.key});
  @override State<WalletPage> createState() => _WalletPageState();
}
class _WalletPageState extends State<WalletPage> {
  int _balance = 0;
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final d = await auth.api('GET', '/wallet');
      setState(() { _balance = d['balance'] ?? 0; _loading = false; });
    } catch (_) { setState(() => _loading = false); }
  }

  Future<void> _topUp() async {
    final ctrl = TextEditingController(text: '10000');
    final amt = await showDialog<int>(context: context, builder: (c) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Text('Top Up Saldo', style: TextStyle(fontWeight: FontWeight.w700)),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        Container(
          padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: kAccent.withAlpha(15), borderRadius: BorderRadius.circular(8)),
          child: Row(children: const [Icon(Icons.info_outline, color: kAccent, size: 18), SizedBox(width: 8), Text('Biaya layanan: Rp 1.000', style: TextStyle(color: kAccent, fontSize: 13))]),
        ),
        const SizedBox(height: 16),
        TextField(controller: ctrl, keyboardType: TextInputType.number, decoration: InputDecoration(prefixText: 'Rp ', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)), filled: true, fillColor: kBg)),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(c, int.tryParse(ctrl.text)), style: FilledButton.styleFrom(backgroundColor: kPrimary), child: const Text('Top Up')),
      ],
    ));
    if (amt != null && amt >= 1000) {
      try {
        await auth.api('POST', '/wallet/top-up', body: {'amount': amt});
        _load();
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Top up Rp $amt berhasil (+ fee Rp 1.000)'), backgroundColor: kGreen));
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e'), backgroundColor: kPrimary));
      }
    }
  }

  String _fmt(int n) => 'Rp ${n.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      // Balance card
      Container(
        width: double.infinity, padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [kDark, Color(0xFF334155)]),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: kDark.withAlpha(40), blurRadius: 16, offset: const Offset(0, 6))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Icon(Icons.account_balance_wallet_rounded, color: kAccent, size: 32),
          const SizedBox(height: 16),
          Text('Saldo tersedia', style: TextStyle(color: Colors.white.withAlpha(150), fontSize: 13)),
          const SizedBox(height: 6),
          Text(_loading ? 'Memuat...' : _fmt(_balance), style: const TextStyle(color: kSurface, fontSize: 32, fontWeight: FontWeight.w800)),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(color: Colors.white.withAlpha(15), borderRadius: BorderRadius.circular(8)),
            child: const Text('Biaya top up: Rp 1.000 • Biaya platform: Rp 1.000/order', style: TextStyle(color: Colors.white70, fontSize: 11)),
          ),
        ]),
      ),
      const SizedBox(height: 20),
      SizedBox(width: double.infinity, height: 48, child: FilledButton.icon(
        onPressed: _topUp, icon: const Icon(Icons.add_rounded), label: const Text('Top Up Saldo'),
        style: FilledButton.styleFrom(backgroundColor: kAccent, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
      )),
      const SizedBox(height: 28),
      const Text('Riwayat Transaksi', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
      const SizedBox(height: 16),
      Center(child: Column(children: [
        Icon(Icons.receipt_long_rounded, size: 48, color: kMuted.withAlpha(50)),
        const SizedBox(height: 8),
        Text('Belum ada aktivitas', style: TextStyle(color: kMuted)),
      ])),
    ]);
  }
}

// ── Trips Page ───────────────────────────────────────────────────────────────
class TripsPage extends StatefulWidget {
  const TripsPage({super.key});
  @override State<TripsPage> createState() => _TripsPageState();
}
class _TripsPageState extends State<TripsPage> {
  List<dynamic> _trips = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final d = await auth.api('GET', '/trips');
      setState(() { _trips = d is List ? d : []; _loading = false; });
    } catch (_) { setState(() => _loading = false); }
  }

  Future<void> _create() async {
    final o = TextEditingController();
    final d = TextEditingController();
    final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Text('Trip Baru', style: TextStyle(fontWeight: FontWeight.w700)),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        TextField(controller: o, decoration: InputDecoration(labelText: 'Asal', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)))),
        const SizedBox(height: 12),
        TextField(controller: d, decoration: InputDecoration(labelText: 'Tujuan', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)))),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(c, true), style: FilledButton.styleFrom(backgroundColor: kAccent), child: const Text('Buat')),
      ],
    ));
    if (ok == true) {
      try {
        await auth.api('POST', '/trips', body: {
          'origin': o.text.trim(), 'destination': d.text.trim(),
          'departure_at': DateTime.now().add(const Duration(days: 7)).toIso8601String(),
          'dp_required': true, 'dp_percent': 50,
        });
        _load();
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e'), backgroundColor: kPrimary));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        const Text('Trip', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
        if (auth.role == 'TRAVELER')
          FilledButton.icon(
            onPressed: _create,
            icon: const Icon(Icons.add, size: 18),
            label: const Text('Baru'),
            style: FilledButton.styleFrom(backgroundColor: kAccent, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
          ),
      ]),
      const SizedBox(height: 16),
      if (_loading) const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator(color: kPrimary))),
      if (!_loading && _trips.isEmpty) Center(child: Column(children: [
        const SizedBox(height: 40),
        Icon(Icons.flight_rounded, size: 56, color: kMuted.withAlpha(50)),
        const SizedBox(height: 12),
        Text('Belum ada jadwal trip', style: TextStyle(color: kMuted, fontSize: 15)),
      ])),
      ..._trips.map((t) => Card(child: ListTile(
        leading: CircleAvatar(backgroundColor: kAccent.withAlpha(18), foregroundColor: kAccent, child: const Icon(Icons.flight_rounded)),
        title: Text('${t['origin']} → ${t['destination']}', style: const TextStyle(fontWeight: FontWeight.w700)),
        subtitle: Text('${t['code']} • ${t['status']}', style: TextStyle(color: kMuted, fontSize: 12)),
        trailing: const Icon(Icons.chevron_right_rounded),
      ))),
    ]);
  }
}

// ── Admin Page ───────────────────────────────────────────────────────────────
class AdminPage extends StatelessWidget {
  const AdminPage({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      const Text('Admin Panel', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
      const SizedBox(height: 16),
      _card(context, Icons.analytics_rounded, 'Transaksi Aktif', 'Monitor semua order aktif', kPrimary, () => context.go('/admin')),
      _card(context, Icons.gavel_rounded, 'Disputes', 'Tinjau dan selesaikan konflik', kAccent, () => context.go('/admin/disputes')),
      _card(context, Icons.verified_user_rounded, 'Verifikasi KYC', 'Setujui atau tolak identitas', kGreen, null),
    ]);
  }

  static Widget _card(BuildContext c, IconData icon, String t, String s, Color color, VoidCallback? onTap) {
    return Padding(padding: const EdgeInsets.only(bottom: 12), child: Card(child: ListTile(
      onTap: onTap,
      leading: CircleAvatar(backgroundColor: color.withAlpha(18), foregroundColor: color, child: Icon(icon)),
      title: Text(t, style: const TextStyle(fontWeight: FontWeight.w700)),
      subtitle: Text(s, style: TextStyle(color: kMuted, fontSize: 12)),
      trailing: onTap != null ? const Icon(Icons.chevron_right_rounded) : null,
    )));
  }
}

// ── Admin Disputes ───────────────────────────────────────────────────────────
class AdminDisputesPage extends StatelessWidget {
  const AdminDisputesPage({super.key});
  @override
  Widget build(BuildContext context) => ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
    const Text('Disputes', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
    const SizedBox(height: 24),
    Center(child: Column(children: [
      Icon(Icons.gavel_rounded, size: 56, color: kMuted.withAlpha(50)),
      const SizedBox(height: 12),
      Text('Tidak ada dispute aktif', style: TextStyle(color: kMuted, fontSize: 15)),
    ])),
  ]);
}

// ── Super Admin Page ─────────────────────────────────────────────────────────
class SuperAdminPage extends StatelessWidget {
  const SuperAdminPage({super.key});
  @override
  Widget build(BuildContext context) => ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
    const Text('Super Admin', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
    const SizedBox(height: 16),
    _card(context, Icons.account_balance_rounded, 'Audit Ledger', 'Cek saldo semua wallet', kPrimary, () => context.go('/superadmin/ledger')),
    _card(context, Icons.tune_rounded, 'Fee Settings', 'Ubah biaya platform & kurir', kAccent, () => context.go('/superadmin/fees')),
    _card(context, Icons.monitor_heart_rounded, 'Monitoring', 'Statistik & performa sistem', kGreen, () => context.go('/superadmin/monitor')),
  ]);

  static Widget _card(BuildContext c, IconData icon, String t, String s, Color color, VoidCallback onTap) {
    return Padding(padding: const EdgeInsets.only(bottom: 12), child: Card(child: ListTile(
      onTap: onTap,
      leading: CircleAvatar(backgroundColor: color.withAlpha(18), foregroundColor: color, child: Icon(icon)),
      title: Text(t, style: const TextStyle(fontWeight: FontWeight.w700)),
      subtitle: Text(s, style: TextStyle(color: kMuted, fontSize: 12)),
      trailing: const Icon(Icons.chevron_right_rounded),
    )));
  }
}

// ── Fees Page ────────────────────────────────────────────────────────────────
class FeesPage extends StatefulWidget {
  const FeesPage({super.key});
  @override State<FeesPage> createState() => _FeesPageState();
}
class _FeesPageState extends State<FeesPage> {
  List<dynamic> _fees = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }
  Future<void> _load() async {
    try {
      final d = await auth.api('GET', '/super-admin/fees');
      setState(() { _fees = d is List ? d : []; _loading = false; });
    } catch (_) { setState(() => _loading = false); }
  }

  String _fmt(int n) => 'Rp ${n.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      const Text('Fee Settings', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
      const SizedBox(height: 16),
      if (_loading) const Center(child: CircularProgressIndicator(color: kPrimary)),
      ..._fees.map((f) => Card(child: ListTile(
        leading: CircleAvatar(backgroundColor: kPrimary.withAlpha(18), foregroundColor: kPrimary, child: const Icon(Icons.monetization_on_rounded)),
        title: Text(f['key'], style: const TextStyle(fontWeight: FontWeight.w700)),
        subtitle: Text('${_fmt(f['amount'] ?? 0)} • ${f['description'] ?? ''}', style: TextStyle(color: kMuted, fontSize: 12)),
      ))),
    ]);
  }
}

// ── Ledger Page ──────────────────────────────────────────────────────────────
class LedgerPage extends StatefulWidget {
  const LedgerPage({super.key});
  @override State<LedgerPage> createState() => _LedgerPageState();
}
class _LedgerPageState extends State<LedgerPage> {
  Map<String, dynamic>? _data;
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }
  Future<void> _load() async {
    try {
      final d = await auth.api('GET', '/super-admin/ledger');
      setState(() { _data = d; _loading = false; });
    } catch (_) { setState(() => _loading = false); }
  }

  String _fmt(int n) => 'Rp ${n.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      const Text('Audit Ledger', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
      const SizedBox(height: 16),
      if (_loading) const Center(child: CircularProgressIndicator(color: kPrimary)),
      if (_data != null) Container(
        width: double.infinity, padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [kDark, Color(0xFF334155)]),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Total Saldo Wallet', style: TextStyle(color: Colors.white.withAlpha(150), fontSize: 13)),
          const SizedBox(height: 6),
          Text(_fmt(_data!['wallet_balances'] ?? 0), style: const TextStyle(color: kSurface, fontSize: 28, fontWeight: FontWeight.w800)),
          const SizedBox(height: 12),
          Text('Jumlah wallet: ${_data!['wallet_count'] ?? 0}', style: TextStyle(color: Colors.white.withAlpha(150), fontSize: 13)),
        ]),
      ),
    ]);
  }
}

// ── Monitor Page ─────────────────────────────────────────────────────────────
class MonitorPage extends StatefulWidget {
  const MonitorPage({super.key});
  @override State<MonitorPage> createState() => _MonitorPageState();
}
class _MonitorPageState extends State<MonitorPage> {
  Map<String, dynamic>? _data;
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }
  Future<void> _load() async {
    try {
      final d = await auth.api('GET', '/super-admin/monitoring');
      setState(() { _data = d; _loading = false; });
    } catch (_) { setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), children: [
      const Text('Monitoring', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
      const SizedBox(height: 16),
      if (_loading) const Center(child: CircularProgressIndicator(color: kPrimary)),
      if (_data != null) ...[
        Row(children: [
          Expanded(child: _stat('Users', '${_data!['users'] ?? 0}', Icons.people_rounded, kPrimary)),
          const SizedBox(width: 12),
          Expanded(child: _stat('Active Orders', '${_data!['active_orders'] ?? 0}', Icons.receipt_long_rounded, kAccent)),
        ]),
        const SizedBox(height: 12),
        _stat('Open Disputes', '${_data!['open_disputes'] ?? 0}', Icons.warning_amber_rounded, Colors.amber),
        const SizedBox(height: 24),
        const Text('Orders by Status', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
        const SizedBox(height: 12),
        ...((_data!['orders_by_status'] as Map?) ?? {}).entries.map((e) => Card(child: ListTile(
          title: Text(e.key, style: const TextStyle(fontWeight: FontWeight.w600)),
          trailing: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(color: kPrimary.withAlpha(15), borderRadius: BorderRadius.circular(8)),
            child: Text('${e.value}', style: const TextStyle(fontWeight: FontWeight.w700, color: kPrimary)),
          ),
        ))),
      ],
    ]);
  }

  static Widget _stat(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: kSurface, borderRadius: BorderRadius.circular(16), border: Border.all(color: kBg)),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(icon, color: color, size: 24),
        const SizedBox(height: 12),
        Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
        const SizedBox(height: 2),
        Text(label, style: TextStyle(color: kMuted, fontSize: 12)),
      ]),
    );
  }
}
