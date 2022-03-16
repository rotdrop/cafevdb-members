import { appName } from '../config.js'

const routes = [
  {
    path: '/',
    props: router => ({
      rootTitle: t(appName, 'Home'),
    }),
  },
  {
    path: '/f/personalProfile',
    component: () => import('../views/PersonalProfile'),
    name: 'personalProfile',
    props: router => ({
      rootTitle: t(appName, 'Personal Profile'),
    }),
  },
  {
    path: '/f/bankAccounts',
    component: () => import('../views/BankAccounts'),
    name: 'bankAccounts',
    props: router => ({
      rootTitle: t(appName, 'Bank Accounts'),
    }),
  },
  {
    path: '/f/instrumentInsurances',
    component: () => import('../views/InstrumentInsurances'),
    name: 'instrumentInsurances',
    props: router => ({
      rootTitle: t(appName, 'Instrument Insurances'),
    }),
  },
  {
    path: '/f/projects',
    component: () => import('../views/Projects'),
    name: 'projects',
    props: router => ({
      rootTitle: t(appName, 'Projects'),
    }),
  },
]

export default routes
