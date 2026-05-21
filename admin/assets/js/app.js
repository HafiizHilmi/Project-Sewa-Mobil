function appData() {
  return {
    /* ── Core State ── */
    activePage: 'dashboard',
    sidebarOpen: false,
    showProfileMenu: false,
    darkMode: false,
    chartMode: 'monthly',

    /* ── Init ── */
    init() {
      this.$watch('darkMode', val => {
        document.documentElement.classList.toggle('dark', val);
      });
    },

    /* ── Navigation ── */
    navItems: [
      { key: 'dashboard', label: 'Dashboard',      icon: 'fa-solid fa-gauge-high' },
      { key: 'cars',      label: 'Car Management', icon: 'fa-solid fa-car'        },
      { key: 'orders',    label: 'Orders',         icon: 'fa-solid fa-clipboard-list'},
      { key: 'customers', label: 'Customers',      icon: 'fa-solid fa-users'      },
      { key: 'settings',  label: 'Settings',       icon: 'fa-solid fa-gear'       },
    ],

    setPage(p) {
      this.activePage = p;
      this.sidebarOpen = false;
      this.showCarDetail = false;
    },

    get pageTitle() {
      return { dashboard:'Dashboard', cars:'Car Management', orders:'Orders', customers:'Customers', settings:'Settings' }[this.activePage];
    },
    get pageSubtitle() {
      return {
        dashboard: 'Apa yang terjadi pada rental kamu hari ini',
        cars: 'Kelola armada kendaraan rental kamu',
        orders: 'Pantau dan kelola semua pesanan rental',
        customers: 'Data pelanggan setia SewaMobil',
        settings: 'Konfigurasi panel administrasi'
      }[this.activePage];
    },

    /* ── Dashboard ── */
    dashAlerts: [
      { icon:'fa-car', bg:'bg-orange-50 dark:bg-orange-900/20', color:'text-orange-400', title:'Vehicle Maintenance Due', desc:'Toyota Avanza 2024 requires scheduled service.', time:'2 hours ago' },
      { icon:'fa-user-plus', bg:'bg-blue-50 dark:bg-blue-900/20', color:'text-blue-500', title:'New Corporate Account', desc:'PT Sukses completed onboarding process.', time:'5 hours ago' },
    ],

    /* ── Customers ── */
    custSearch: '',
    custStatus: 'Semua',
    selectedCustomer: null,
    showCustomerDetail: false,
    customers: [
      { id:1, name:'Sucipto Haryono', email:'sucipto12@gmail.com', phone:'081234567890', city:'Surabaya', status:'Aktif', totalOrders:12, lastOrder:'2025-04-27', avatarColor:'#3b82f6', address:'Jl. Basuki Rahmat No. 12, Surabaya' },
      { id:2, name:'Dewi Rahmawati', email:'dewi.rahma@gmail.com', phone:'082345678901', city:'Surabaya', status:'Baru', totalOrders:2, lastOrder:'2025-04-27', avatarColor:'#8b5cf6', address:'Jl. Pemuda No. 88, Surabaya' },
      { id:3, name:'Budi Santoso', email:'budi.s89@gmail.com', phone:'083456789012', city:'Gresik', status:'Nonaktif', totalOrders:7, lastOrder:'2025-04-27', avatarColor:'#64748b', address:'Jl. Pahlawan No. 5, Gresik' },
    ],
    get filteredCustomers() {
      return this.customers.filter(c => {
        const mSrch = !this.custSearch || c.name.toLowerCase().includes(this.custSearch.toLowerCase());
        const mStat = this.custStatus === 'Semua' || c.status === this.custStatus;
        return mSrch && mStat;
      });
    },
    openCustomerDetail(c) {
      this.selectedCustomer = c;
      this.showCustomerDetail = true;
    },

    /* ── Settings / Admin ── */
    activeTab: 'preferences',
    showAddAdminModal: false,
    emailNotifs: true,
    pushNotifs: false,
    autoRefresh: true,
    settingsTabs: [
      { key: 'admin', label: 'Admin Management' },
      { key: 'business', label: 'Business Settings' },
      { key: 'preferences', label: 'Preferences' },
    ],
    admins: [
      { id:1, name:'Ahmad Fauzi', email:'ahmad@sewamobilsby.id', role:'Super Admin', addedAt:'1 Jan 2025', color:'#3b82f6' },
      { id:2, name:'Dewi Rahma', email:'dewi@sewamobilsby.id', role:'Staff', addedAt:'10 Feb 2025', color:'#8b5cf6' },
    ],

    /* ── Cars ── */
    carFilter: 'All',
    showCarDetail: false,
    selectedCar: null,
    showEditModal: false,
    editingCarId: null,
    editModalTitle: '',
    editForm: {},
    cars: [
      { id:1, name:'Toyota Avanza', year:'2024', plate:'B 1242 DFR', category:'MPV', status:'Tersedia', price:'Rp 350.000', fuel:'Bensin - Pertalite', transmission:'Manual', passengers:'7', chassis:'MHFAB8GM4N4001234', renter:'Windah Basudara', rentalStart:'18 Mei 2025', rentalEnd:'22 Mei 2025', rentalPct:60, rentalLeft:'2 Hari Lagi', iconCls:'text-blue-400', bgCls:'bg-blue-50 dark:bg-blue-900/30' },
      { id:2, name:'Toyota Kijang Innova', year:'2023', plate:'B 1562 DYF', category:'MPV', status:'Tersewa', price:'Rp 550.000', fuel:'Bensin - Pertamax', transmission:'Matic', passengers:'8', chassis:'MHFAG8GM4N4005678', renter:'Budi Santoso', rentalStart:'15 Mei 2025', rentalEnd:'25 Mei 2025', rentalPct:50, rentalLeft:'5 Hari Lagi', iconCls:'text-indigo-400', bgCls:'bg-indigo-50 dark:bg-indigo-900/30' },
      { id:3, name:'Mercedes Benz E300', year:'2023', plate:'B 84 IK', category:'Sedan', status:'Perbaikan', price:'Rp 2.000.000', fuel:'Bensin Turbo', transmission:'Matic', passengers:'5', chassis:'WDD2130561A012345', renter:'-', rentalStart:'-', rentalEnd:'-', rentalPct:0, rentalLeft:'-', iconCls:'text-slate-400', bgCls:'bg-slate-100 dark:bg-slate-700/50' },
      { id:4, name:'BYD M6', year:'2024', plate:'B 1238', category:'EV', status:'Tersedia', price:'Rp 550.000', fuel:'Listrik (EV)', transmission:'Matic', passengers:'7', chassis:'LGXCE4CB8N6023456', renter:'-', rentalStart:'-', rentalEnd:'-', rentalPct:0, rentalLeft:'-', iconCls:'text-teal-400', bgCls:'bg-teal-50 dark:bg-teal-900/30' },
    ],
    get filteredCars() {
      if (this.carFilter === 'All') return this.cars;
      return this.cars.filter(c => c.category === this.carFilter);
    },
    carBadge(status) {
      return { 'Tersedia': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'Tersewa': 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400', 'Perbaikan': 'bg-red-100 text-red-500 dark:bg-red-900/30 dark:text-red-400' }[status] || 'bg-slate-100 text-slate-500';
    },
    viewCar(car) {
      this.selectedCar = car;
      this.showCarDetail = true;
    },
    openEditCarModal(car) {
      this.editingCarId = car.id;
      this.editModalTitle = 'Edit Data Kendaraan';
      this.editForm = {
        name: car.name,
        plate: car.plate,
        chassis: car.chassis,
        category: car.category,
        price: car.price.replace(/[^\d]/g,''),
        fuel: car.fuel,
        engine: car.engine,
        passengers: parseInt(car.passengers),
        transmission: car.transmission
      };
      this.showEditModal = true;
    },
    openAddCarModal() {
      this.editingCarId = null;
      this.editModalTitle = 'Tambah Kendaraan Baru';
      this.editForm = { name:'', plate:'', chassis:'', category:'', price:'', fuel:'', engine:'', passengers:'', transmission:'' };
      this.showEditModal = true;
    },
    saveCar() {
      this.showEditModal = false;
      alert(this.editingCarId ? 'Perubahan disimpan (Demo)' : 'Kendaraan baru ditambahkan (Demo)');
    },

    /* ── Orders ── */
    orderFilter: 'All Orders',
    showOrderModal: false,
    selectedOrder: null,
    orders: [
      { idLine1:'#202604', idLine2:'278301', customer:{ name:'Sucipto', email:'sucipto12@gmail.com', phone:'081234567890' }, car:{ name:'Toyota Avanza', category:'MPV', fuel:'Bensin', thumbBg:'bg-blue-50 dark:bg-blue-900/30', thumbColor:'text-blue-400' }, startDate:'Apr 27, 10.00 WIB', endDate:'Apr 30, 10.00 WIB', totalFormatted:'1.050.000', status:'Confirmed' },
      { idLine1:'#202604', idLine2:'278302', customer:{ name:'Budi Santoso', email:'budi.s@gmail.com', phone:'082345678901' }, car:{ name:'Toyota Kijang Innova', category:'MPV', fuel:'Bensin', thumbBg:'bg-indigo-50 dark:bg-indigo-900/30', thumbColor:'text-indigo-400' }, startDate:'Apr 27, 09.00 WIB', endDate:'Apr 30, 09.00 WIB', totalFormatted:'2.750.000', status:'Pending' },
      { idLine1:'#202604', idLine2:'278303', customer:{ name:'Dewi Rahma', email:'dewi.r@gmail.com', phone:'083456789012' }, car:{ name:'Toyota Fortuner VRZ', category:'SUV', fuel:'Solar', thumbBg:'bg-emerald-50 dark:bg-emerald-900/30', thumbColor:'text-emerald-400' }, startDate:'Apr 28, 08.00 WIB', endDate:'May 2, 08.00 WIB', totalFormatted:'3.500.000', status:'Completed' },
      { idLine1:'#202604', idLine2:'278304', customer:{ name:'Ahmad Fauzi', email:'ahmad.f@gmail.com', phone:'084567890123' }, car:{ name:'BYD M6', category:'EV', fuel:'Listrik', thumbBg:'bg-teal-50 dark:bg-teal-900/30', thumbColor:'text-teal-400' }, startDate:'Apr 29, 10.00 WIB', endDate:'May 1, 10.00 WIB', totalFormatted:'1.100.000', status:'Completed' },
    ],
    get filteredOrders() {
      if (this.orderFilter === 'All Orders') return this.orders;
      return this.orders.filter(o => o.status === this.orderFilter);
    },
    openOrderModal(order) {
      this.selectedOrder = order;
      this.showOrderModal = true;
    }

  };
}
