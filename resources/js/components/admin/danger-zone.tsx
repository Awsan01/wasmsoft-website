import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion } from '@/types/admin';
import { useConfirm } from './confirm';
import { Section } from './section';

/** Deletes an item after confirmation (the server checks the permission again). */
export function DeleteButton({ deletion, block = false }: { deletion: Deletion; block?: boolean }) {
    const { t } = useTranslation();
    const confirm = useConfirm();
    const [processing, setProcessing] = useState(false);

    const remove = async () => {
        if (processing || !(await confirm({ message: t('admin.content.confirm_delete', { name: deletion.name }), accept: t('admin.actions.delete') }))) {
            return;
        }

        router.delete(deletion.url, { onStart: () => setProcessing(true), onFinish: () => setProcessing(false) });
    };

    return (
        <Button variant="danger" size="sm" icon="trash" block={block} onClick={remove} aria-disabled={processing || undefined} className={block ? undefined : 'justify-self-start'}>
            {t('admin.actions.delete')}
        </Button>
    );
}

/** The section at the end of a form that deletes the item. */
export function DangerZone({ deletion }: { deletion: Deletion | null }) {
    const { t } = useTranslation();

    if (!deletion) {
        return null;
    }

    return (
        <Section title={t('admin.content.danger_title')} description={deletion.description}>
            <DeleteButton deletion={deletion} />
        </Section>
    );
}
