const receipt = document.getElementById('receipt');
const totalEl = document.getElementById('total');
const errorEl = document.getElementById('error');
const toast = document.getElementById('toast');
const voidUrl = document.body.dataset.voidUrl;

function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toast.classList.remove('show'), 2500);
}

function bumpTotal() {
    totalEl.classList.remove('bump');
    void totalEl.offsetWidth;
    totalEl.classList.add('bump');
}

function renderReceipt(items) {
    if (items.length === 0) {
        receipt.innerHTML = '<div class="empty">No items scanned yet.</div>';
        return;
    }
    receipt.innerHTML = items.map(item => `
        <div class="line-item" data-sku="${item.sku}">
            <span class="sku">${item.sku}</span>
            <span class="qty">&times;${item.quantity}</span>
            <span class="subtotal">${(item.subtotal / 100).toFixed(2)}</span>
            <form action="${voidUrl}" method="POST" class="ajax-form void-form">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="sku" value="${item.sku}">
                <button type="submit" class="void-btn" title="Void one ${item.sku}">&times;</button>
            </form>
        </div>
    `).join('');
}

function applyState(state) {
    errorEl.textContent = '';
    renderReceipt(state.items);
    totalEl.textContent = (state.total / 100).toFixed(2);
    bumpTotal();
}

const csrfToken = document.querySelector('input[name="_token"]').value;

async function submitAjaxForm(form) {
    const formData = new FormData(form);

    let response;
    try {
        response = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
        });
    } catch (networkError) {
        form.submit(); 
        return;
    }

    let data = {};
    try {
        data = await response.json();
    } catch (parseError) {
        form.submit();
        return;
    }

    if (! response.ok) {
        const message = data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Something went wrong.';
        errorEl.textContent = message;
        showToast(message);
        return;
    }

    applyState(data);
}

document.body.addEventListener('submit', (event) => {
    const form = event.target;
    if (! form.classList.contains('ajax-form')) {
        return;
    }
    event.preventDefault();
    submitAjaxForm(form);
});