Bcl4Tags = {
    init : function()
    {
        Osynapsy.element('body').on('click','.bcl4-tags', this.tagInputFocus);
        Osynapsy.element('body').on('keypress','.bcl4-tags-input', this.tagInput);
        Osynapsy.element('body').on('input','.bcl4-tags-input', this.tagInputResize);
        Osynapsy.element('body').on('click','.bcl4-tags-delete', this.tagDelete);
    },
    tagInputFocus : function(event)
    {
        let input = event.target.querySelector('input[type="text"]');
        if (input) {
            input.focus();
        }
    },
    tagInput : function(event)
    {
        if (event.keyCode !== 13) {
            //this.size = this.value.length + 1;
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        let badge = Bcl4Tags.badgeFactory(this);
        this.parentElement.insertBefore(badge, this);
        this.value = '';
        this.size = 1;
        Bcl4Tags.updateHiddenField(this.closest('.bcl4-tags'));
    },
    tagInputResize : function() {
       this.size = this.value.length + 1;
    },
    tagDelete : function()
    {
        let wrapper = this.closest('.bcl4-tags');
        let parent = this.parentNode;
        parent.remove();
        Bcl4Tags.updateHiddenField(wrapper);
        wrapper.click();
    },
    badgeFactory : function(obj)
    {
        let label = obj.value;
        let objectId = obj.closest('.bcl4-tags').getAttribute('id').replace('Box', '');
        let wrapper = document.createElement('h5');
        wrapper.classList.add('d-inline');
        wrapper.append(Bcl4Tags.badgeHiddenBoxFactory(objectId, label));
        let badge = wrapper.appendChild(document.createElement('span'));
        badge.classList.add('badge', 'badge-primary', 'badge-xl' ,'mr-1');
        badge.append(label, ' ', this.badgeDeleteFactory());
        return wrapper;
    },
    badgeHiddenBoxFactory : function(id, label)
    {
        let hiddenBox = document.createElement('input');
        hiddenBox.setAttribute('type', 'hidden');
        hiddenBox.setAttribute('name', '__' + id + '[]');
        hiddenBox.setAttribute('value', label);
        return hiddenBox;
    },
    badgeDeleteFactory : function()
    {
        let button = document.createElement('i');
        button.classList.add('fa', 'fa-times', 'bcl4-tags-delete');
        return button;
    },
    updateHiddenField : function(tagWrapper)
    {
        let values = '';
        tagWrapper.querySelectorAll('.badge').forEach(function(badge) {
            values += '[' + badge.innerText.trim() + ']';
        });
        tagWrapper.querySelector('input[type="hidden"]').value = values;
        tagWrapper.dispatchEvent(new Event('change'));
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('Bcl4Tags',function(){
        Bcl4Tags.init();
    });
}
