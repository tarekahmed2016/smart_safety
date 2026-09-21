<script setup>
import { computed } from 'vue'
import { faBolt } from '@fortawesome/free-solid-svg-icons'
import { faBoxesStacked } from '@fortawesome/free-solid-svg-icons'
import { faBuilding } from '@fortawesome/free-solid-svg-icons'
import { faBullseye } from '@fortawesome/free-solid-svg-icons'
import { faCertificate } from '@fortawesome/free-solid-svg-icons'
import { faCheck } from '@fortawesome/free-solid-svg-icons'
import { faClock } from '@fortawesome/free-solid-svg-icons'
import { faCube } from '@fortawesome/free-solid-svg-icons'
import { faEnvelope } from '@fortawesome/free-solid-svg-icons'
import { faFileLines } from '@fortawesome/free-solid-svg-icons'
import { faFlag } from '@fortawesome/free-solid-svg-icons'
import { faGear } from '@fortawesome/free-solid-svg-icons'
import { faGlobe } from '@fortawesome/free-solid-svg-icons'
import { faHandshake } from '@fortawesome/free-solid-svg-icons'
import { faHeadset } from '@fortawesome/free-solid-svg-icons'
import { faHouse } from '@fortawesome/free-solid-svg-icons'
import { faLeaf } from '@fortawesome/free-solid-svg-icons'
import { faLocationDot } from '@fortawesome/free-solid-svg-icons'
import { faLock } from '@fortawesome/free-solid-svg-icons'
import { faPaperPlane } from '@fortawesome/free-solid-svg-icons'
import { faPhone } from '@fortawesome/free-solid-svg-icons'
import { faScrewdriverWrench } from '@fortawesome/free-solid-svg-icons'
import { faScroll } from '@fortawesome/free-solid-svg-icons'
import { faSeedling } from '@fortawesome/free-solid-svg-icons'
import { faShieldHalved } from '@fortawesome/free-solid-svg-icons'
import { faSliders } from '@fortawesome/free-solid-svg-icons'
import { faStethoscope } from '@fortawesome/free-solid-svg-icons'
import { faTrophy } from '@fortawesome/free-solid-svg-icons'
import { faUserTie } from '@fortawesome/free-solid-svg-icons'
import { faUsers } from '@fortawesome/free-solid-svg-icons'
import { faUtensils } from '@fortawesome/free-solid-svg-icons'
import { faWheatAwn } from '@fortawesome/free-solid-svg-icons'
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons'

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const icons = {
  handshake: faHandshake,
  experience: faClock,
  gear: faGear,
  settings: faGear,
  tools: faScrewdriverWrench,
  flexible: faSliders,
  sliders: faSliders,
  quality: faCertificate,
  certificate: faCertificate,
  check: faCheck,
  vision: faBullseye,
  bullseye: faBullseye,
  mission: faScroll,
  scroll: faScroll,
  'file-lines': faFileLines,
  food: faUtensils,
  agri: faWheatAwn,
  industry: faBuilding,
  building: faBuilding,
  packing: faBoxesStacked,
  boxes: faBoxesStacked,
  cube: faCube,
  home: faHouse,
  medical: faStethoscope,
  shield: faShieldHalved,
  safety: faShieldHalved,
  security: faLock,
  support: faHeadset,
  trophy: faTrophy,
  globe: faGlobe,
  flag: faFlag,
  bolt: faBolt,
  sprout: faSeedling,
  leaf: faLeaf,
  users: faUsers,
  'user-tie': faUserTie,
  phone: faPhone,
  envelope: faEnvelope,
  location: faLocationDot,
  'location-dot': faLocationDot,
  whatsapp: faWhatsapp,
  send: faPaperPlane,
  products: faBoxesStacked,
}

const resolvedIcon = computed(() => icons[props.name] || faShieldHalved)
</script>

<template>
  <font-awesome-icon :icon="resolvedIcon" class="px-line-icon" aria-hidden="true" />
</template>
