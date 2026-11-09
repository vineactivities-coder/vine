document.addEventListener('DOMContentLoaded', () => {
    const category = document.getElementById('shop-category');
    const subcategory = document.getElementById('shop-subcategory');
    function sync() {
        for (const option of subcategory.options) {
            const compatible = !option.value || !category.value || option.dataset.category === category.value;
            option.hidden = !compatible;
            option.disabled = !compatible;
            if (!compatible && option.selected) subcategory.value = '';
        }
    }
    category.addEventListener('change', sync);
    subcategory.addEventListener('change', () => {
        if (subcategory.value) category.value = subcategory.selectedOptions[0].dataset.category;
        sync();
    });
    sync();
});
