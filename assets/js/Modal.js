window.Osynapsy = window.Osynapsy || {'modal' : {}};

class Modal
{
    constructor()
    {
        // Rileva la presenza di Bootstrap 5 native o Bootstrap 4 (jQuery)
        this.isBs5 = typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function';
        this.dismissAttr = this.isBs5 ? 'data-bs-dismiss' : 'data-dismiss';
    }

    buttonCloseFactory()
    {
        // In BS5 si usa la classe .btn-close senza contenuto, in BS4 .close con &times;
        let attributes = {'type' : 'button'};
        attributes[this.dismissAttr] = 'modal';

        if (this.isBs5) {
            attributes['class'] = 'btn-close';
            return this.createElement('button', attributes);
        }

        attributes['class'] = 'close';
        let button = this.createElement('button', attributes);
        button.innerHTML = '&times;';
        return button;
    }

    buttonFactory(label, remoteAction, extraClass)
    {
        let attributes = {
            'type' : 'button',
            'class' : 'btn ' + extraClass
        };
        attributes[this.dismissAttr] = 'modal';

        let button = this.createElement('button', attributes);
        button.innerHTML = label;

        if (remoteAction) {
            let action = remoteAction.replace(')', '').split('(');
            button.classList.add('click-execute');
            button.dataset.action = action[0];
            button.dataset.actionParameters = action[1] ? action[1] : null;
        }
        return button;
    }

    createElement(tag, attributes = {})
    {
        let element = document.createElement(tag);
        for (let attributeId in attributes) {
            element.setAttribute(attributeId, attributes[attributeId]);
        }
        return element;
    }

    create(id, title, body, actionConfirm, actionCancel)
    {
        this.modal = this.createElement('div', {'id' : id, 'class' : 'modal fade', 'tabindex' : '-1', 'role' : 'dialog'});
        this.modal.dialog = this.modal.appendChild(this.createElement('div', {'class' : 'modal-dialog modal-dialog-centered', 'role' : 'document'}));
        this.modal.content = this.modal.dialog.appendChild(this.createElement('div', {'class' : 'modal-content'}));
        this.modal.content.appendChild(this.headerFactory(title));
        this.modal.content.appendChild(this.bodyFactory(body));
        this.modal.content.appendChild(this.footFactory(actionConfirm, actionCancel));
        
        // Inserisce il nodo nel DOM ed esegue l'inizializzazione BS4/BS5
        document.body.appendChild(this.modal);
        this.show(id);

        return this.modal;
    }

    show(id)
    {
        let modalEl = document.getElementById(id);
        if (!modalEl) return;

        if (this.isBs5) {
            let modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, { keyboard: true });
            modalInstance.show();
        } else if (typeof $ === 'function' && typeof $().modal === 'function') {
            $(modalEl).modal({ keyboard: true });
        }
    }

    headerFactory(title)
    {
        let header = this.createElement('div', {'class' : 'modal-header bg-light'});
        header.appendChild(this.titleFactory(title));
        header.appendChild(this.buttonCloseFactory());
        return header;
    }

    titleFactory(title)
    {
        let titleContainer = this.createElement('h5', {'class' : 'modal-title'});
        titleContainer.innerHTML = title;
        return titleContainer;
    }

    bodyFactory(body)
    {
        this.modal.bodyContainer = this.createElement('div', {'class' : 'modal-body'});
        this.modal.bodyContainer.innerHTML = body;
        return this.modal.bodyContainer;
    }

    footFactory(actionConfirm, actionCancel)
    {
        let footContainer = this.createElement('div', {'class' : 'modal-footer'});
        if (actionConfirm) {
            // Sostituito float pull-right con float-end (o float-right in BS4)
            footContainer.appendChild(this.buttonFactory('Conferma', actionConfirm, 'btn-primary float-end float-right'));
        }
        if (actionCancel !== false) {
            footContainer.appendChild(this.buttonFactory('Chiudi', actionCancel, 'btn-secondary' + (!actionConfirm ? ' float-end float-right' : '')));
        }
        return footContainer;
    }
}

// Metodo Factory centrale richiesto
Osynapsy.modal.create = function(id, title, body, actionConfirm, actionCancel)
{
    this.remove();
    let modalFactory = new Modal();
    return modalFactory.create(id, title, body, actionConfirm, actionCancel);
};

Osynapsy.modal.remove = function()
{
    let el = document.getElementById('amodal');
    if (el) {
        // Distruzione istanza nativa se presente (BS5)
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
            let inst = bootstrap.Modal.getInstance(el);
            if (inst) { inst.hide(); inst.dispose(); }
        } else if (typeof $ === 'function' && typeof $().modal === 'function') {
            $(el).modal('hide');
        }
        el.remove();
    }
    // Rimuove la classe backdrop se rimasta appesa
    let backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(b => b.remove());
};

Osynapsy.modal.confirm = function(title, message, actionConfirm)
{
    return this.create('amodal', title ? title : 'Conferma', message, actionConfirm);
};

Osynapsy.modal.alert = function(title, message)
{
    return this.create('amodal', title ? title : 'Alert', message);
};

Osynapsy.modal.window = function(title, url, width = '640px', height = '480px')
{
    let modalHeight = Osynapsy.isEmpty(height) ? ($(window).innerHeight() - 250) + 'px' : height;
    let modalWidth  = Osynapsy.isEmpty(width) ? null : width;
    
    // Invocazione del metodo unificato create
    let modal = this.create('amodal', title ? title : 'No title', '', false, false);
    let modalFactory = new Modal();

    let spinner = modalFactory.createElement('i', {'class' : 'fa fa-spinner fa-spin', 'style' : 'font-size: 24px; position: absolute; top: 48%; left: 50%; color:silver;'});
    let iframe = modalFactory.createElement('iframe', {'onload' : "this.previousElementSibling.style.display = 'none'; this.style.visibility = 'visible';", 'name' : 'amodal', 'style' : 'visibility:hidden; width: 100%; height:'+ modalHeight +'; border: 0px; border-radius: 3px;', 'border' : '0'});
    
    if (!Array.isArray(url)) {
        iframe.src = url;
    }
    if (!Osynapsy.isEmpty(modalWidth) && window.screen.availWidth > 1000) {
        modal.querySelector('.modal-dialog').style.maxWidth = modalWidth;
    }

    let bodyContainer = modal.querySelector('.modal-body');
    bodyContainer.appendChild(spinner);
    bodyContainer.appendChild(iframe);
    
    let footer = modal.querySelector('.modal-footer');
    if (footer) { footer.remove(); }

    if (Array.isArray(url)) {
        let form = $(url[1]);
        let action = form.attr('action');
        let target = form.attr('target');
        let method = form.attr('method');
        form.attr('target', 'amodal');
        form.attr('method', 'POST');
        form.attr('action', url[0]);
        form.submit();
        form.attr('action', action ? action : '');
        form.attr('target', target ? target : '');
        form.attr('method', method ? method : '');
    }
};