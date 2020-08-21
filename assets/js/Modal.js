class Modal
{
    buttonCloseFactory()
    {
        let button = this.createElement('button', {'type' : 'button', 'class' : 'close', 'data-dismiss' : 'modal'});
        button.innerHTML = '&times;';
        return button;
    }
    
    buttonFactory(label, remoteAction, extraClass)
    {        
        let button = this.createElement('button', {'type' : 'button', 'class' : 'btn '+extraClass, 'data-dismiss' : 'modal'});
        button.innerHTML = label;
        if (remoteAction) {
            let action = remoteAction.replace(')','').split('(');
            button.dataAction = action[0];
            button.dataActionParameters = action[1] ? action[1] : null;
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
        this.modal = this.createElement('div', {'id' : id, 'class' : 'modal', 'role' : 'dialog'});
        this.modal.dialog = this.modal.appendChild(this.createElement('div', {'class' : 'modal-dialog modal-dialog-centered', 'role' : 'document'}));
        this.modal.content = this.modal.dialog.appendChild(this.createElement('div', {'class' : 'modal-content'}));
        this.modal.content.appendChild(this.headerFactory(title));
        this.modal.content.appendChild(this.bodyFactory(body));
        this.modal.content.appendChild(this.footFactory(actionConfirm, actionCancel));
        return this.modal;
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
            footContainer.appendChild(this.buttonFactory('Conferma', actionConfirm, 'btn-primary pull-right'));
        }
        if (actionCancel !== false) {
            footContainer.appendChild(this.buttonFactory('Annulla', actionCancel, 'btn-secondary' + (!actionConfirm ? ' pull-right' : '')));
        }
        return footContainer;
    }
}

function modalConfirm(title, message, actionConfirm)
{    
    let modalFactory = new Modal();
    let modal = modalFactory.create('amodal', title ? title : 'Conferma', message, actionConfirm);        
    document.body.appendChild(modal);
    $('#amodal').modal({'keyboard' : true});
}

function modalAlert(title, message)
{
    let modalFactory = new Modal();
    let modal =  modalFactory.create('amodal', title ? title : 'Alert', message);
    document.body.appendChild(modal);
    $('#amodal').modal({'keyboard' : true});
}

function modalWindow(title, url, width = '640px', height = '480px')
{
    let modalHeight = Osynapsy.isEmpty(height) ? ($(window).innerHeight() - 250) + 'px' : height;
    let modalWidth  = Osynapsy.isEmpty(width) ? null : width;     
    let modalFactory = new Modal();
    let modal = modalFactory.create('amodal', title ? title : 'No title', '', false, false);
    let spinner = modalFactory.createElement('i', {'class' : 'fa fa-spinner fa-spin', 'style' : 'font-size: 24px; position: absolute; top: 48%; left: 50%; color:silver;'});
    let iframe = modalFactory.createElement('iframe', {'onload' : "$(this).prev().hide(); $(this).css('visibility','');", 'name' : 'amodal', 'style' : 'visibility:hidden; width: 100%; height:'+ modalHeight +'; border: 0px; border-radius: 3px;', 'border' : '0'});
    if (!Array.isArray(url)) {
        iframe.src = url;
    }
    if (!Osynapsy.isEmpty(modalWidth) && window.screen.availWidth > 1000) {
        modal.dialog.style = 'max-width : ' + modalWidth;
    }
    modal.bodyContainer.appendChild(spinner);
    modal.bodyContainer.appendChild(iframe);    
    modal.dialog.querySelector('.modal-footer').remove();
    document.body.appendChild(modal);
    if (Array.isArray(url)) {    
        let form = url[1];
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
    $('#amodal').modal({'keyboard' : true});
}