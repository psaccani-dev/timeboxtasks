// resources/js/composables/useTranslations.ts

import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useTranslations() {
    const page = usePage()

    // Pegar as traduções do Inertia com tipo correto
    const translations = computed<Record<string, string>>(() => {
        return (page.props.translations as Record<string, string>) || {}
    })

    // Função de tradução simples
    const __ = (key: string, replacements: Record<string, string> = {}): string => {
        // Pegar a tradução
        let translation = translations.value[key] || key

        // Substituir placeholders se houver
        Object.keys(replacements).forEach(placeholder => {
            translation = translation.replace(`:${placeholder}`, replacements[placeholder])
        })

        return translation
    }

    return {
        __,
        translations
    }
}