import { createContext, type ReactNode, useCallback, useContext, useId, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';
import { useModalDialog } from './dialog';

type ConfirmOptions = {
    message: string;
    title?: string;
    accept?: string;
};

type Request = ConfirmOptions & { resolve: (confirmed: boolean) => void };

const ConfirmContext = createContext<(options: ConfirmOptions) => Promise<boolean>>(() => Promise.resolve(false));

/**
 * Asks before destructive or irreversible actions:
 * `if (await confirm({ message })) router.delete(url)`.
 */
export function useConfirm() {
    return useContext(ConfirmContext);
}

/** Provides useConfirm() and renders its dialog (once per layout). */
export function ConfirmProvider({ children }: { children: ReactNode }) {
    const [request, setRequest] = useState<Request | null>(null);

    const confirm = useCallback((options: ConfirmOptions) => new Promise<boolean>((resolve) => setRequest({ ...options, resolve })), []);

    const finish = useCallback(
        (confirmed: boolean) => {
            request?.resolve(confirmed);
            setRequest(null);
        },
        [request],
    );

    return (
        <ConfirmContext.Provider value={confirm}>
            {children}
            <ConfirmDialog request={request} onFinish={finish} />
        </ConfirmContext.Provider>
    );
}

/** Focus starts on "Cancel", the safe choice. */
function ConfirmDialog({ request, onFinish }: { request: Request | null; onFinish: (confirmed: boolean) => void }) {
    const { t } = useTranslation();
    const modal = useModalDialog(request !== null, () => onFinish(false));
    const id = useId();

    return (
        <dialog
            {...modal}
            // The texts are only rendered while a confirmation is asked for.
            aria-labelledby={request ? `${id}-title` : undefined}
            aria-describedby={request ? `${id}-message` : undefined}
            className="m-auto w-[calc(100vw-2rem)] max-w-md overflow-hidden rounded-xl border border-border bg-surface p-0 text-ink shadow-lg"
        >
            {request && (
                <>
                    <div className="flex items-start gap-4 px-6 pt-6 pb-5">
                        <span className="grid size-10 shrink-0 place-items-center rounded-full bg-error-soft text-error">
                            <Icon name="alert-triangle" />
                        </span>
                        <div className="grid gap-1.5">
                            <h2 id={`${id}-title`} className="text-h3">
                                {request.title ?? t('ui.are_you_sure')}
                            </h2>
                            <p id={`${id}-message`} className="text-sm text-muted">
                                {request.message}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-wrap justify-end gap-3 border-t border-border bg-canvas px-6 py-4">
                        <Button variant="outline" autoFocus onClick={() => onFinish(false)}>
                            {t('ui.cancel')}
                        </Button>
                        <Button variant="danger" onClick={() => onFinish(true)}>
                            {request.accept ?? t('ui.confirm')}
                        </Button>
                    </div>
                </>
            )}
        </dialog>
    );
}
