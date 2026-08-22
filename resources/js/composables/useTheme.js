import { ref } from 'vue';

const isDark = ref(false);

export function useTheme() {
    const applyTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    };

    const toggleTheme = () => {
        isDark.value = !isDark.value;
        applyTheme();
    };

    const initTheme = () => {
        const saved = localStorage.getItem('theme');
        if (saved) {
            isDark.value = saved === 'dark';
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();
    };

    return { isDark, toggleTheme, initTheme };
}
