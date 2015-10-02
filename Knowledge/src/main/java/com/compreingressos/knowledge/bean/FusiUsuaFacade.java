/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.FuncaoSistema;
import com.compreingressos.knowledge.model.FusiUsua;
import com.compreingressos.knowledge.model.Usuario;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.NoResultException;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class FusiUsuaFacade extends AbstractFacade<FusiUsua> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public FusiUsuaFacade() {
        super(FusiUsua.class);
    }
    
    public List<FuncaoSistema> findPai() {
        return getEntityManager().createNamedQuery("FuncaoSistema.findPai").getResultList();
    }

    public List<FuncaoSistema> findFilho(Integer fusiId) {
        return getEntityManager().createNamedQuery("FuncaoSistema.findFilho").setParameter("Id", fusiId).getResultList();
    }    

    public List<FusiUsua> findSelectedByUsuario(Usuario usuaId) {
        return getEntityManager().createNamedQuery("FusiUsua.findSelectedByUsuario").setParameter("usuaId", usuaId).getResultList();
    }

    public Integer findByUsuarioEFuncao(FuncaoSistema fusiId, Usuario usuaId) {
        try {
            return (Integer) getEntityManager().createNamedQuery("FusiUsua.findByUsuarioEFuncao").setParameter("usuaId", usuaId.getId()).setParameter("fusiId", fusiId.getId()).getSingleResult();
        } catch (NoResultException ex) {
            return 0;
        }
    }

    public List<FusiUsua> findAllSummary() {
        return getEntityManager().createNamedQuery("FusiUsua.findAllSummary").getResultList();
    }        

    public List<FuncaoSistema> findAllByUsuario() {
        return getEntityManager().createNamedQuery("FusiUsua.findAllByUsuario")
                .getResultList();
    }

    public void remove(Usuario usuario) {
        getEntityManager().createNativeQuery("DELETE FROM DIM_KBASE_FUSI_USUA WHERE ID_USUA = "+ usuario.getId()).executeUpdate();
    }
    
    public void remove(FuncaoSistema funcaoSistema, Usuario usuario){
        getEntityManager().createNativeQuery("DELETE FROM DIM_KBASE_FUSI_USUA WHERE ID_USUA = "+ usuario.getId() +" AND ID_FUSI = "+ funcaoSistema.getId()).executeUpdate();
    }
    
}
