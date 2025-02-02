import './bootstrap';
import { createApp } from 'vue';

import Alpine from 'alpinejs';
import Work from './components/Work.vue';
import Kub from './components/Kub.vue';
import Shear from './components/Shear.vue';

const app = createApp({
  components: {

        Work,
        Kub,
        Shear
  },
})
app.mount('#app')

window.Alpine = Alpine;

Alpine.start();
