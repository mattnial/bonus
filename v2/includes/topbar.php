<header class="h-16 bg-white shadow flex items-center justify-between px-6 z-10">
    <div class="flex items-center">
        <button class="text-gray-500 focus:outline-none lg:hidden mr-4">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <h1 class="text-xl font-bold text-gray-800" id="pageTitle">
            <?php echo ucfirst($vista ?? 'Panel General'); ?>
        </h1>
    </div>

    <div class="flex items-center space-x-4">
        <div class="relative">
            <button onclick="toggleNotifications()" class="relative p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-bell fa-lg"></i>
                <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white hidden flex items-center justify-center min-w-[20px]">
                    0
                </span>
            </button>

            <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden transform transition-all origin-top-right">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase">Notificaciones</h3>
                </div>
                <div id="notifList" class="max-h-64 overflow-y-auto">
                    <p class="text-center text-gray-400 text-xs py-4">Sin novedades</p>
                </div>
            </div>
        </div>

        <div onclick="openProfileModal()" class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition select-none">
            <div class="text-right hidden md:block">
                <p class="text-sm font-bold text-gray-700 user-name-display"><?php echo $_SESSION['name'] ?? 'Usuario'; ?></p>
                <p class="text-xs text-gray-500"><?php echo $_SESSION['role'] ?? 'Staff'; ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-blue-200 flex items-center justify-center text-blue-700 font-bold text-xl overflow-hidden border-2 border-blue-100 relative">
                <span id="userAvatarInitial">U</span>
                <img id="userAvatarImg" src="" class="h-full w-full object-cover absolute inset-0 hidden">
            </div>
        </div>
    </div>
</header>