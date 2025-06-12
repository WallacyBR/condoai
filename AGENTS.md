# AGENT Instructions

This repository uses Laravel with Vue.js. Please follow these guidelines:

1. **DRY Principle**: Avoid duplicating logic. Create reusable components and helper functions where appropriate.
2. **Vue.js Reactivity**: Implement front-end reactivity using Vue.js components. Integrate them via Laravel's Vite setup.
3. **Testing**: Provide automated tests for every feature.
   - Backend: Use `phpunit`.
   - Frontend: Use `jest` for Vue.js components.
4. **PSR-12 Code Style**: Ensure PHP code follows PSR-12.
5. **Práticas de Commit**: mantenha commits focados e descritivos; todas as mensagens de commit e comentários no código devem ser escritas em português detalhado. Este padrão é obrigatório.

6. **Environment Variables**: Never commit real credentials. Use `.env.example` as reference for new env variables.
7. **Directory Structure**: Keep `resources/js` for Vue components, `tests` for PHP tests, and `resources/tests` for Jest tests.
8. **Pull Requests**: Ensure CI passes (`phpunit` and `npm test`) before submitting.
