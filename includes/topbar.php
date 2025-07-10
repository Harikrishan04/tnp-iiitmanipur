<header class="bg-white shadow-sm w-full flex-shrink-0">
                <div class="flex items-center justify-between px-4 py-3 md:px-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-t from-green-500 to-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">TNP</span>
                        </div>
                        <h1 class="text-lg md:text-2xl font-bold text-gray-800">Training & Placement Portal</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline text-blue-800 font-medium">
                            <i class="fas fa-user mr-2"></i>Hi, <span id="userRole"><?php echo htmlspecialchars(ucfirst($user_name)); ?></span>
                        </span>
                        <button id="mobileSidebarBtn" class="md:hidden text-gray-700 focus:outline-none">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                    </div>
                </div>
 </header>