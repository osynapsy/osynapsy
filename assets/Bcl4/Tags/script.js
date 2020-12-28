Bcl4Tags = {
    init : function()
    {
        Osynapsy.element('body').on('click','.bcl4-tags', this.tagInputFocus);
        Osynapsy.element('body').on('keypress','.bcl4-tags-input', this.tagInput);
        Osynapsy.element('body').on('click','.bcl4-tags-delete', this.tagDelete);
    },
    tagInputFocus : function(event)
    {
        event.target.querySelector('input[type=text]').focus();
    },
    tagInput : function(event)
    {
        if (event.keyCode !== 13) {
            this.size = this.value.length + 1;
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        let badge = Bcl4Tags.badgeFactory(this.value);
        this.parentElement.insertBefore(badge, this);
        this.value = '';
        this.size = 1;
        Bcl4Tags.updateHiddenField(this.closest('.bcl4-tags'));
    },
    tagDelete : function()
    {
        let wrapper = this.closest('.bcl4-tags');
        let parent = this.parentNode;
        parent.remove();
        Bcl4Tags.updateHiddenField(wrapper);
        wrapper.click();
    },
    badgeFactory : function(label)
    {
        let wrapper = document.createElement('h5');
        wrapper.classList.add('d-inline');
        let badge = wrapper.appendChild(document.createElement('span'));
        badge.classList.add('badge', 'badge-primary', 'badge-xl' ,'mr-1');
        badge.append(label, ' ', this.badgeDeleteFactory());
        return wrapper;
    },
    badgeDeleteFactory : function()
    {
        let button = document.createElement('i');
        button.classList.add('fa', 'fa-close', 'bcl4-tags-delete');
        return button;
    },
    updateHiddenField : function(tagWrapper)
    {
        let values = '';
        tagWrapper.querySelectorAll('.badge').forEach(function(badge) {
            values += '[' + badge.innerText.trim() + ']';
        });
        tagWrapper.querySelector('input[type="hidden"]').value = values;
    }
};

if (window.Osynapsy){
    Osynapsy.plugin.register('Bcl4Tags',function(){
        Bcl4Tags.init();
    });
}
