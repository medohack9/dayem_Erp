<div id="modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center" dir="rtl">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 id="modal-title" class="text-lg font-bold text-[#111111]"></h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div id="modal-content" class="text-gray-600"></div>
    </div>
</div>

<script>
function openModal(title, content) {
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-content').innerHTML = content;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-overlay').classList.add('hidden');
}

document.getElementById('modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>