import './bootstrap';
import { createApp } from 'vue';

import Alpine from 'alpinejs';
import Work from './components/Work.vue';

const app = createApp({
  components: {

    Work

  },
})
app.mount('#app')

window.Alpine = Alpine;

Alpine.start();
