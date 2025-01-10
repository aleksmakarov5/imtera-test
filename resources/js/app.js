import './bootstrap';
import { createApp } from 'vue';

import Alpine from 'alpinejs';
import Work from './components/Work.vue';
import Kub from './components/Kub.vue';

const app = createApp({
  components: {

        Work,
        Kub
  },
})
app.mount('#app')

window.Alpine = Alpine;

Alpine.start();
