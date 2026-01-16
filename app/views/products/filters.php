<!-- Sidebar Filters -->
<aside class="w-full lg:w-72 shrink-0 flex flex-col gap-8 lg:sticky lg:top-28">
    <!-- Category Accordions -->
    <div class="flex flex-col gap-4">
        <h3 class="text-lg font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">filter_list</span> Bộ lọc
        </h3>
        <div class="flex flex-col gap-3">
            <details class="group bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 overflow-hidden" open>
                <summary class="flex cursor-pointer items-center justify-between p-4 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <span class="font-semibold text-sm">Loại cây</span>
                    <span class="material-symbols-outlined text-lg transition-transform duration-200 group-open:rotate-180">expand_more</span>
                </summary>
                <div class="px-4 pb-4 pt-0 text-sm space-y-2 text-text-secondary dark:text-gray-400">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                                <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox" name="category[]" value="<?= $cat['id'] ?>"/>
                                <?= clean($cat['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                            <input checked class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                            Cây để bàn
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                            <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                            Cây phong thủy
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                            <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                            Sen đá
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                            <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                            Bonsai
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                            <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                            Cây trong nhà
                        </label>
                    <?php endif; ?>
                </div>
            </details>
            
            <details class="group bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 overflow-hidden">
                <summary class="flex cursor-pointer items-center justify-between p-4 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <span class="font-semibold text-sm">Vị trí đặt</span>
                    <span class="material-symbols-outlined text-lg transition-transform duration-200 group-open:rotate-180">expand_more</span>
                </summary>
                <div class="px-4 pb-4 pt-0 text-sm space-y-2 text-text-secondary dark:text-gray-400">
                    <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                        <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                        Văn phòng
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                        <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                        Ban công
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                        <input class="rounded border-gray-300 text-primary focus:ring-primary bg-transparent" type="checkbox"/>
                        Phòng ngủ
                    </label>
                </div>
            </details>
        </div>
    </div>
    
    <!-- Price Slider -->
    <div class="flex flex-col gap-3">
        <div class="flex justify-between items-center">
            <h4 class="text-sm font-bold">Khoảng giá</h4>
            <span class="text-xs text-primary font-medium cursor-pointer">Reset</span>
        </div>
        <div class="bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 p-4">
            <div class="relative h-10 w-full flex items-center">
                <div class="h-1.5 w-full bg-[#d2e4da] dark:bg-gray-700 rounded-full">
                    <div class="absolute h-1.5 bg-primary rounded-full left-[20%] right-[30%]"></div>
                </div>
                <!-- Left Thumb -->
                <div class="absolute left-[20%] -ml-2 size-5 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
                <!-- Right Thumb -->
                <div class="absolute right-[30%] -mr-2 size-5 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
            </div>
            <div class="flex justify-between text-xs font-semibold text-text-main dark:text-gray-300 mt-2">
                <span>150k</span>
                <span>1.2tr</span>
            </div>
        </div>
    </div>
    
    <!-- Tags / Chips -->
    <div class="flex flex-col gap-3">
        <h4 class="text-sm font-bold">Đặc điểm</h4>
        <div class="flex flex-wrap gap-2">
            <button class="px-4 py-2 rounded-full text-xs font-semibold bg-primary text-white shadow-sm shadow-primary/30 transition-all hover:bg-primary-dark">
                Dễ chăm sóc
            </button>
            <button class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary hover:border-primary hover:text-primary transition-all">
                Ưa bóng râm
            </button>
            <button class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary hover:border-primary hover:text-primary transition-all">
                Lọc không khí
            </button>
            <button class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary hover:border-primary hover:text-primary transition-all">
                Tặng quà
            </button>
            <button class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary hover:border-primary hover:text-primary transition-all">
                An toàn thú cưng
            </button>
        </div>
    </div>
</aside>
