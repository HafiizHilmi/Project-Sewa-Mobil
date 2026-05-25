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
      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page');
      if (page && ['dashboard', 'verifications', 'cars', 'orders', 'customers', 'settings'].includes(page)) {
        this.activePage = page;
      }
    },

    /* ── Navigation ── */
    navItems: [
      { key: 'dashboard', label: 'Dashboard',      icon: 'fa-solid fa-gauge-high' },
      { key: 'verifications', label: 'Verifications', icon: 'fa-solid fa-id-card' },
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
      return { dashboard:'Dashboard', verifications:'Verifications', cars:'Car Management', orders:'Orders', customers:'Customers', settings:'Settings' }[this.activePage];
    },
    get pageSubtitle() {
      return {
        dashboard: 'Apa yang terjadi pada rental kamu hari ini',
        verifications: 'Tinjau identitas pelanggan untuk keamanan sewa',
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
    customers: (window.SERVER_CUSTOMERS || []).map(o => {
      const statusMap = {
        'verified': 'Aktif',
        'pending': 'Baru',
        'unverified': 'Nonaktif',
        'rejected': 'Suspended'
      };
      const status = statusMap[o.verification_status] || 'Nonaktif';

      const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#ef4444'];
      const index = o.name ? (o.name.charCodeAt(0) % colors.length) : 0;
      const avatarColor = colors[index];

      const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return d.toLocaleDateString('id-ID', options);
      };

      return {
        id: o.id,
        name: o.name,
        email: o.email,
        phone: o.phone || '-',
        city: 'Surabaya',
        status: status,
        totalOrders: parseInt(o.total_orders || 0),
        lastOrder: formatDate(o.last_order),
        avatarColor: avatarColor,
        address: o.address || 'Belum ada transaksi (alamat belum terekam)',
        totalSpent: parseFloat(o.total_spent || 0),
        totalSpentFormatted: new Intl.NumberFormat('id-ID').format(parseFloat(o.total_spent || 0)),
        totalDamaged: parseInt(o.total_damaged || 0),
        rentedCars: (o.rented_cars || '').split('|').filter(Boolean).map(str => {
          const parts = str.split('::');
          return { name: parts[0] || 'Unknown', plate: parts[1] || 'Pending' };
        })
      };
    }),
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
    showEditCustModal: false,
    editingCust: null,
    openEditCustModal(c) {
      this.editingCust = c;
      this.showEditCustModal = true;
    },
    selectedCustIds: [],
    get allCustSelected() {
      if (this.filteredCustomers.length === 0) return false;
      return this.filteredCustomers.every(c => this.selectedCustIds.includes(c.id));
    },
    toggleSelectAllCust() {
      if (this.allCustSelected) {
        const filteredIds = this.filteredCustomers.map(c => c.id);
        this.selectedCustIds = this.selectedCustIds.filter(id => !filteredIds.includes(id));
      } else {
        const filteredIds = this.filteredCustomers.map(c => c.id);
        this.selectedCustIds = [...new Set([...this.selectedCustIds, ...filteredIds])];
      }
    },

    /* ── Verifications ── */
    verifications: window.SERVER_VERIFICATIONS || [],
    verifyFilter: 'Semua',
    selectedVerification: null,
    showVerificationDetail: false,

    get filteredVerifications() {
      if (this.verifyFilter === 'Semua') return this.verifications;
      return this.verifications.filter(v => v.verification_status === this.verifyFilter.toLowerCase());
    },
    openVerificationDetail(v) {
      this.selectedVerification = v;
      this.showVerificationDetail = true;
    },
    closeVerificationDetail() {
      this.showVerificationDetail = false;
      this.selectedVerification = null;
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
    orders: (window.SERVER_ORDERS || []).map(o => {
      let thumbBg = 'bg-blue-50 dark:bg-blue-900/30';
      let thumbColor = 'text-blue-400';
      const cat = (o.category || '').toUpperCase();
      if (cat === 'SUV') {
        thumbBg = 'bg-emerald-50 dark:bg-emerald-900/30';
        thumbColor = 'text-emerald-400';
      } else if (cat === 'MPV') {
        thumbBg = 'bg-blue-50 dark:bg-blue-900/30';
        thumbColor = 'text-blue-400';
      } else if (cat === 'SEDAN') {
        thumbBg = 'bg-slate-100 dark:bg-slate-700/50';
        thumbColor = 'text-slate-400';
      } else if (cat === 'EV') {
        thumbBg = 'bg-teal-50 dark:bg-teal-900/30';
        thumbColor = 'text-teal-400';
      }

      const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const options = { month: 'short', day: 'numeric' };
        return d.toLocaleDateString('en-US', options) + ', 10.00 WIB';
      };

      const rawStatus = o.status || 'pending';
      const status = rawStatus.charAt(0).toUpperCase() + rawStatus.slice(1);

      const createdDate = new Date(o.created_at || Date.now());
      const year = createdDate.getFullYear();
      const month = String(createdDate.getMonth() + 1).padStart(2, '0');
      const idLine1 = `#${year}${month}`;
      const idLine2 = String(o.id).padStart(6, '0');

      const totalFormatted = new Intl.NumberFormat('id-ID').format(o.total_price);

      return {
        id: o.id,
        idLine1: idLine1,
        idLine2: idLine2,
        customer: {
          name: o.full_name,
          email: o.email,
          phone: o.phone
        },
        car: {
          name: `${o.make} ${o.model}`,
          category: o.category,
          fuel: o.fuel_type,
          thumbBg: thumbBg,
          thumbColor: thumbColor
        },
        startDate: formatDate(o.start_date),
        endDate: formatDate(o.end_date),
        totalFormatted: totalFormatted,
        status: status,
        assignedPlate: o.assigned_plate || null,
        additionalCost: o.additional_cost ? parseFloat(o.additional_cost) : 0,
        damageImage: o.damage_image || null,
        damageDescription: o.damage_description || null
      };
    }),
    get filteredOrders() {
      if (this.orderFilter === 'All Orders') return this.orders;
      return this.orders.filter(o => o.status === this.orderFilter);
    },
    openOrderModal(order) {
      this.selectedOrder = order;
      this.showOrderModal = true;
    },
    showCompleteModal: false,
    completingOrder: null,
    openCompleteModal(order) {
      this.completingOrder = order;
      this.showCompleteModal = true;
    },
    skipComplete(id) {
      if (confirm('Selesaikan sewa kendaraan ini tanpa biaya tambahan atau kerusakan?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'order_action.php';
        
        const bookingIdInput = document.createElement('input');
        bookingIdInput.type = 'hidden';
        bookingIdInput.name = 'booking_id';
        bookingIdInput.value = id;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'complete';

        const skipInput = document.createElement('input');
        skipInput.type = 'hidden';
        skipInput.name = 'skip_damage';
        skipInput.value = '1';
        
        form.appendChild(bookingIdInput);
        form.appendChild(actionInput);
        form.appendChild(skipInput);
        document.body.appendChild(form);
        form.submit();
      }
    }

  };
}
